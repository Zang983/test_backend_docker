<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TransactionRepository $transactionRepository,
        private readonly BudgetRepository $budgetRepository,
        private readonly PotsRepository $potsRepository)
    {
        {
            parent::__construct($registry, User::class);

        }
    }

        /**
         * Used to upgrade (rehash) the user's password automatically over time.
         */
        public
        function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
            }

            $user->setPassword($newHashedPassword);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }


        public
        function findUserDashboardData(int $numberOfTransactions = 5, ?User $user = null): array
        {
            if (!$user) {
                return [];
            }
            return [
                'transaction' => $this->transactionRepository->findLastTransactionsByUser($user, $numberOfTransactions),
                'budget' => $this->budgetRepository->findByUserWithoutTransactions($user),
                'pots' => $this->potsRepository->findPotsByUserWithTotal($user),
                'recurring'=>$this->transactionRepository->findRecurringDataByUser($user),
                'userBalance'=>$user->getBalance(),
                'userIncome'=> $this->transactionRepository->findAndCalculateLast30DaysIncome($user),
                'userExpenses'=> $this->transactionRepository->findAndCalculateLast30DaysExpenses($user),
            ];
        }
        public function findByUserAllDatas(User $user): array
        {
            return [
                'transaction' => $this->transactionRepository->findByUserWithParties($user, ['recurringBills' => false])->getQuery()->getArrayResult(),
                'budget' => $this->budgetRepository->findByUserWithoutTransactions($user),
                'pots' => $this->potsRepository->findPotsByUserWithTotal($user),
            ];
        }


        //    /**
        //     * @return User[] Returns an array of User objects
        //     */
        //    public function findByExampleField($value): array
        //    {
        //        return $this->createQueryBuilder('u')
        //            ->andWhere('u.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->orderBy('u.id', 'ASC')
        //            ->setMaxResults(10)
        //            ->getQuery()
        //            ->getResult()
        //        ;
        //    }

        //    public function findOneBySomeField($value): ?User
        //    {
        //        return $this->createQueryBuilder('u')
        //            ->andWhere('u.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->getQuery()
        //            ->getOneOrNullResult()
        //        ;
        //    }
    }