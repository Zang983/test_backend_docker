<?php

namespace App\Repository;

use App\Entity\Pots;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pots>
 */
class PotsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pots::class);
    }

    public function findPotsByUserWithTotal(User $user, ?int $limit = 4): array
    {

        return [
            'pots' => $this->createQueryBuilder('p')
            ->where('p.ownerUser = :user')
            ->setParameter('user', $user->getId())
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult(),
            'total' => $this->createQueryBuilder('p')
            ->select('SUM(p.balance)')
            ->where('p.ownerUser = :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult(),

        ];

    }

    //    /**
    //     * @return Pots[] Returns an array of Pots objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Pots
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
