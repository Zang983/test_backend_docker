<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findAllByUserWithParties(User $user,  array $params): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->leftJoin('t.parties', 'p')
            ->addSelect('p')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId());

        if ($params['field'] === 'date')
            $queryBuilder->orderBy('t.transectedAt', $params['order']);
        elseif ($params['field'] === 'amount')
            $queryBuilder->orderBy('t.amount', $params['order']);
        elseif ($params['field'] === 'alphabetical')
            $queryBuilder->orderBy('p.name', $params['order']);
        else
            $queryBuilder->orderBy('t.transectedAt', 'ASC');
        if (isset($params['category'])) {
            $queryBuilder->andWhere('t.category = :category')
                ->setParameter('category', $params['category']);
        }

        return $queryBuilder;

    }

//    public function findAllWithParties() : array
//    {
//        return $this->createQueryBuilder('t')
//            ->addSelect('p')
//            ->leftJoin('t.parties', 'p')
//            ->getQuery()
//            ->getResult();
//    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
