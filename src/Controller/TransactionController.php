<?php

namespace App\Controller;

use App\Entity\Party;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\PartyRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TransactionController extends AbstractController
{
    private array $categories;

    #[NoReturn]
    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    #[Route('/transactions/', name: 'app_transaction', methods: ['GET'])]
    public function getTransactions(TransactionRepository $repo, SerializerInterface $serializer, Request $request, Security $security, int $page = 1): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        $params = ['field' => 'date', 'order' => 'ASC', 'recurringBills' => false, 'search' => ""]; // Valeurs par défaut
        $page = $request->query->getInt('page', 1);
        if ($request->query->has('field')) {
            $field = $request->query->get('field');
            if (in_array($field, ['date', 'amount', 'alphabetical'], true)) {
                $params['field'] = $field;
            }
        }
        if ($request->query->has('order')) {
            $order = strtoupper($request->query->get('order'));
            if (in_array($order, ['ASC', 'DESC'])) {
                $params['order'] = $order;
            }
        }
        if ($request->query->has('category')) {
            $category = $request->query->get('category');
            if (in_array($category, $this->categories, true)) {
                $params['category'] = $category;
            }
        }
        if ($request->query->has('recurringBills')) {
            $params['recurringBills'] = filter_var(
                $request->query->get('recurringBills'),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if ($request->query->has('search')) {
            $search = $request->query->get('search');
            if (is_string($search) || is_numeric($search)) {
                $params['search'] = trim((string) $search);
            }
        }

        $queryBuilder = $repo->findByUserWithParties($user, $params);
        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(10);
        if ($pagerfanta->getNbPages() < $page) {
            return new JsonResponse(['error' => 'Page not found!!!!!!!!!!!!'], Response::HTTP_NOT_FOUND);
        }
        $pagerfanta->setCurrentPage($page);


        $json = $serializer->serialize($pagerfanta, 'json', ['groups' => ['transaction:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/transaction', name: 'add_transaction', methods: ['POST'])]
    public function addTransaction(Request $request, ValidatorInterface $validator, PartyRepository $partyRepo, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['transaction']) || !isset($data['party'])) {
            return new JsonResponse(['error' => 'No data found'], Response::HTTP_BAD_REQUEST);
        }

        $category = $data['transaction']['category'] ?? null;
        $amount = $data['transaction']['amount'] ?? null;
        $partyName = $data['party']['name'];
        if (!$category || !$amount || !$partyName) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
        if (!in_array($category, $this->getParameter('categories'))) {
            return new JsonResponse(['error' => 'Invalid category'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = new Transaction();
        $transaction->setCategory($category)
            ->setAmount($amount)
            ->setUserOwner($user)
            ->setTransectedAt(new \DateTimeImmutable());
        $party = $partyRepo->findOneBy(['name' => $partyName]) ?? (new Party())->setName($partyName);
        $transaction->setParties($party);
        $party->addTransaction($transaction);

        $errors = [
            'party' => $validator->validate($party),
            'transaction' => $validator->validate($transaction),
        ];

        $errorMessages = [];
        foreach ($errors as $entityName => $violations) {
            foreach ($violations as $violation) {
                $errorMessages[] = "{$entityName}: " . $violation->getMessage();
            }
        }
        if (!empty($errorMessages)) {
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $manager->persist($transaction);
        $manager->persist($party);
        $manager->flush();

        return new JsonResponse("Nouvelle entrée créée ! ", Response::HTTP_CREATED, [], true);
    }

    #[Route('/transaction/{id}', name: 'delete_transaction', methods: ['DELETE'])]
    public function deleteTransaction(EntityManagerInterface $manager, Security $security, Transaction $transaction = null): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$transaction) {
            return new JsonResponse(['error' => 'Transaction not found'], Response::HTTP_NOT_FOUND);
        }
        if ($transaction->getUserOwner() !== $user) {
            return new JsonResponse(['error' => 'You are not authorized to delete this transaction'], Response::HTTP_FORBIDDEN);
        }
        $manager->remove($transaction);
        $manager->flush();

        return new JsonResponse(['message' => 'Transaction deleted'], Response::HTTP_OK);
    }
}