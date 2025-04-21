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

        // Gestion des tris
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

        // Filtre par catégorie
        if (isset($params['category'])) {
            $queryBuilder->andWhere('t.category = :category')
                ->setParameter('category', $params['category']);
        }

        return $queryBuilder;
    }

    // Gardez vos autres méthodes existantes ici...
}