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

    public function findAllByUserWithParties(User $user, array $params): QueryBuilder
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

    public function getLastTransactionsByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('t.transectedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    public function getRecurringData(User $user, bool $debug = false): array
    {
        $recurringTransactions = $this->findBy([
            'userOwner' => $user,
            'isRecurring' => true
        ]);

        $now = new \DateTime();
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');
        $nextWeek = (new \DateTime())->modify('+7 days');

        $paidRecurring = 0;
        $remainingRecurring = 0;
        $upcomingTransactions = 0;
        $ignoredTransactions = 0; // Pour le debug

        foreach ($recurringTransactions as $transaction) {
            $amount = $transaction->getAmount();

            try {
                $projectedDate = (new \DateTime())
                    ->setDate(
                        (int)$now->format('Y'),
                        (int)$now->format('m'),
                        min((int)$transaction->getTransectedAt()->format('d'), (int)$endOfMonth->format('d'))
                    )
                    ->setTime(
                        (int)$transaction->getTransectedAt()->format('H'),
                        (int)$transaction->getTransectedAt()->format('i'),
                        (int)$transaction->getTransectedAt()->format('s')
                    );

                if ($projectedDate >= $startOfMonth && $projectedDate <= $endOfMonth) {
                    if ($projectedDate <= $now) {
                        $paidRecurring += $amount;
                    } else {
                        if ($projectedDate <= $nextWeek) {
                            $upcomingTransactions += $amount;
                        } else {
                            $remainingRecurring += $amount;
                        }
                    }
                } else {
                    $ignoredTransactions += $amount;
                }
            } catch (\Exception $e) {
                // Log l'erreur si nécessaire
                $ignoredTransactions += $amount;
            }
        }

        $result = ['paid_recurring' => round($paidRecurring,2),
            'remaining_recurring' => round($remainingRecurring,2),
            'upcoming_transactions' => round($upcomingTransactions,2)];

        if($debug) {
            $result['ignored_transactions'] = $ignoredTransactions;
            $result['total_in_calculation'] = $paidRecurring + $remainingRecurring + $upcomingTransactions;
            $result['total_with_ignored'] = $paidRecurring + $remainingRecurring + $upcomingTransactions + $ignoredTransactions;
        }

        return $result;
    }

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