<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDataShare;
use App\Repository\UserDataShareRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/userdata/share')]
class UserDataShareController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private UserDataShareRepository $repo) {}

    #[Route('', methods: ['GET'])]
    public function listShares(): JsonResponse
    {
        $u = $this->getUser();
        $owned = $this->repo->findBy(['owner' => $u]);
        $incoming = $this->repo->findBy(['target' => $u]);
        $map = fn(UserDataShare $s) => [
            'id' => $s->getId(),
            'owner' => $s->getOwner()?->getEmail(),
            'target' => $s->getTarget()?->getEmail(),
            'namespace' => $s->getNamespace(),
            'status' => $s->getStatus(),
            'canWrite' => $s->canWrite(),
        ];
        return $this->json([
            'owned' => array_map($map, $owned),
            'incoming' => array_map($map, $incoming)
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $req): JsonResponse
    {
        $payload = json_decode($req->getContent() ?: 'null', true);
        if (!is_array($payload)) return $this->json(['error' => 'Invalid'], 400);
        $ns = strtolower((string)($payload['namespace'] ?? ''));
        $email = (string)($payload['email'] ?? '');
        $canWrite = (bool)($payload['canWrite'] ?? false);
        if (!preg_match('/^[a-z0-9_\-]{1,32}$/i', $ns) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid input'], 400);
        }
        if (!in_array($ns, ['shopping','recipes'], true)) {
            return $this->json(['error' => 'Namespace not shareable'], 400);
        }
        $owner = $this->getUser();
        $target = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$target) return $this->json(['error' => 'User not found'], 404);
        if ($target->getId() === $owner->getId()) return $this->json(['error' => 'Self share not allowed'], 400);

        $share = (new UserDataShare())
            ->setOwner($owner)
            ->setTarget($target)
            ->setNamespace($ns)
            ->setCanWrite($canWrite)
            ->setStatus(UserDataShare::STATUS_PENDING);
        $this->em->persist($share);
        $this->em->flush();
        return $this->json(['ok' => true, 'id' => $share->getId()]);
    }

    #[Route('/{id}/respond', methods: ['POST'])]
    public function respond(int $id, Request $req): JsonResponse
    {
        $payload = json_decode($req->getContent() ?: 'null', true);
        if (!is_array($payload)) return $this->json(['error' => 'Invalid'], 400);
        $action = $payload['action'] ?? '';
        $share = $this->repo->find($id);
        if (!$share || $share->getTarget()?->getId() !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Not found'], 404);
        }
        if ($action === 'accept') { $share->setStatus(UserDataShare::STATUS_ACCEPTED); }
        elseif ($action === 'decline') { $share->setStatus(UserDataShare::STATUS_DECLINED); }
        else { return $this->json(['error' => 'Invalid action'], 400); }
        $this->em->flush();
        return $this->json(['ok' => true]);
    }
}
