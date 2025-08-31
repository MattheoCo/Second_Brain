<?php

namespace App\Controller;

use App\Entity\UserData;
use App\Repository\UserDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/userdata')]
class UserDataController extends AbstractController
{
    public function __construct(
    private EntityManagerInterface $em,
    private UserDataRepository $repo
    ) {}

    #[Route('/{ns}', name: 'api_userdata_get', methods: ['GET'])]
    public function getNs(string $ns): JsonResponse
    {
        $user = $this->getUser();
        $row = $this->repo->findOneByUserAndNs($user->getId(), $ns);
        $defaults = [
            'finance' => [
                'accounts' => [
                    ['id' => bin2hex(random_bytes(16)), 'name' => 'Compte courant', 'initial' => 0]
                ],
                'txns' => [],
                'budgets' => []
            ],
            'agenda' => ['events' => []],
            'tasks' => [],
            'recipes' => [],
            'shopping' => [],
        ];
        $decoded = $row ? json_decode($row->getData() ?: 'null', true) : null;
        $isAssoc = is_array($decoded) && array_keys($decoded) !== range(0, count($decoded ?? []) - 1);
        if ($ns === 'finance') {
            // Expect an object-like structure; if not, return defaults
            $state = $isAssoc ? (array)$decoded : ($defaults['finance'] ?? []);
        } else {
            // For arrays (recipes, shopping, tasks, agenda.events), accept sequential arrays; otherwise defaults
            if ($ns === 'agenda') {
                // agenda expects { events: [] }, but accept legacy "[]" by wrapping
                if ($isAssoc) {
                    $state = (array)$decoded;
                    if (!isset($state['events']) || !is_array($state['events'])) {
                        $state['events'] = [];
                    }
                } elseif (is_array($decoded)) {
                    // Sequential array -> treat as the events list
                    $state = ['events' => $decoded];
                } else {
                    $state = $defaults['agenda'] ?? ['events' => []];
                }
            } else {
                $state = is_array($decoded) ? $decoded : ($defaults[$ns] ?? []);
            }
        }
        return $this->json(['state' => $state]);
    }

    #[Route('/{ns}', name: 'api_userdata_put', methods: ['PUT'])]
    public function putNs(string $ns, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $userContextId = $user->getId();
        $payload = json_decode($request->getContent() ?: 'null', true);
        if (!is_array($payload) || !array_key_exists('state', $payload)) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }
    $state = is_array($payload['state']) ? $payload['state'] : [];

    $row = $this->repo->findOneByUserAndNs($userContextId, $ns);
        if (!$row) {
            $row = (new UserData())
                ->setUser($user)
                ->setNamespace($ns)
                ->setData(json_encode($state, JSON_UNESCAPED_UNICODE));
            $this->em->persist($row);
        } else {
            $row->setData(json_encode($state, JSON_UNESCAPED_UNICODE));
        }
        $this->em->flush();

        return $this->json(['ok' => true]);
    }
}
