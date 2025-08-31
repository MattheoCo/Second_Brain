<?php

namespace App\Repository;

use App\Entity\UserData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, UserData::class); }

    public function findOneByUserAndNs(int $userId, string $ns): ?UserData
    {
        return $this->createQueryBuilder('d')
            ->andWhere('IDENTITY(d.user) = :u')->setParameter('u', $userId)
            ->andWhere('d.namespace = :ns')->setParameter('ns', $ns)
            ->getQuery()->getOneOrNullResult();
    }
}
