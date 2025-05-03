<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByUserWithParties(User $user, array $params): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->leftJoin('t.parties', 'p')
            ->addSelect('p')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId());

        // Gestion du filtre des transactions récurrentes
        $queryBuilder->andWhere('t.isRecurring = :isRecurring')
            ->setParameter('isRecurring', $params['recurringBills']);

        // Gestion de la recherche
        if (!empty($params['search'])) {
            $searchTerm = trim($params['search']);
            if (is_numeric($searchTerm)) {
                // Recherche par montant
                $queryBuilder->andWhere('t.amount = :amount')
                    ->setParameter('amount', (float)$searchTerm);
            } else {
                // Recherche par nom de partie (émetteur/receveur)
                $queryBuilder->andWhere('LOWER(p.name) LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
            }
        }

        if(isset($params['field'])){
            switch ($params['field']) {
                case 'date':
                    $queryBuilder->orderBy('t.transectedAt', $params['order']);
                    break;
                case 'amount':
                    $queryBuilder->orderBy('t.amount', $params['order']);
                    break;
                case 'alphabetical':
                    $queryBuilder->orderBy('p.name', $params['order']);
                    break;
                default:
                    $queryBuilder->orderBy('t.transectedAt', 'ASC');
            }
        }


        // Filtre par catégorie
        if (isset($params['category'])) {
            $queryBuilder->andWhere('t.category = :category')
                ->setParameter('category', $params['category']);
        }

        return $queryBuilder;
    }

    public function findLastTransactionsByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('t.transectedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    public function findRecurringDataByUser(User $user, bool $debug = false): array
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

    public function findAndCalculateMonthIncome(User $user){
        $transactions = $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('t.transectedAt >= :startDate')
            ->setParameter('startDate', new \DateTime('first day of this month'))
            ->andWhere('t.transectedAt <= :endDate')
            ->setParameter('endDate', new \DateTime('last day of this month'))
            ->getQuery()
            ->getArrayResult();

        $totalIncome = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['amount'] > 0) {
                $totalIncome += $transaction['amount'];
            }
        }

        return round($totalIncome,2);
    }
    public function findAndCalculateLast30DaysIncome(User $user){
        $transactions = $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('t.transectedAt >= :startDate')
            ->setParameter('startDate', new \DateTime('first day of this month'))
            ->andWhere('t.transectedAt <= :endDate')
            ->setParameter('endDate', new \DateTime('last day of this month'))
            ->getQuery()
            ->getArrayResult();

        $totalExpenses = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['amount'] > 0) {
                $totalExpenses += abs($transaction['amount']);
            }
        }

        return round($totalExpenses,2);
    }
    public function findAndCalculateLast30DaysExpenses(User $user){
        $transactions = $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('t.transectedAt >= :startDate')
            ->setParameter('startDate', new \DateTime('-30 days'))
            ->andWhere('t.transectedAt <= :endDate')
            ->setParameter('endDate', new \DateTime())
            ->getQuery()
            ->getArrayResult();

        $totalExpenses = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['amount'] < 0) {
                $totalExpenses += abs($transaction['amount']);
            }
        }
        return round($totalExpenses,2);
    }
    public function findAndCalculateMonthExpenses(User $user){
        $transactions = $this->createQueryBuilder('t')
            ->andWhere('t.userOwner = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('t.transectedAt >= :startDate')
            ->setParameter('startDate', new \DateTime('first day of this month'))
            ->andWhere('t.transectedAt <= :endDate')
            ->setParameter('endDate', new \DateTime('last day of this month'))
            ->getQuery()
            ->getArrayResult();

        $totalExpenses = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['amount'] < 0) {
                $totalExpenses += abs($transaction['amount']);
            }
        }
        return round($totalExpenses,2);
    }


}