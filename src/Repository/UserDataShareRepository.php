<?php
namespace App\Repository;

use App\Entity\UserDataShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserDataShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, UserDataShare::class); }

    /** @return UserDataShare[] */
    public function acceptedForTargetAndNs(int $targetId, string $ns): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.target) = :t')->setParameter('t', $targetId)
            ->andWhere('s.namespace = :ns')->setParameter('ns', strtolower($ns))
            ->andWhere('s.status = :st')->setParameter('st', UserDataShare::STATUS_ACCEPTED)
            ->getQuery()->getResult();
    }

    /** @return UserDataShare[] */
    public function acceptedForOwnerAndNs(int $ownerId, string $ns): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.owner) = :o')->setParameter('o', $ownerId)
            ->andWhere('s.namespace = :ns')->setParameter('ns', strtolower($ns))
            ->andWhere('s.status = :st')->setParameter('st', UserDataShare::STATUS_ACCEPTED)
            ->getQuery()->getResult();
    }
}
