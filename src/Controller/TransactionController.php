<?php

namespace App\Controller;

use App\Entity\Party;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\PartyRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/transaction', name: 'app_transaction', methods: ['GET'])]
    public function getTransactions(TransactionRepository $repo, SerializerInterface $serializer, Security $security): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        $transactions = $repo->findBy(['userOwner' => $user->getId()]);
        $json = $serializer->serialize($transactions, 'json', ['groups' => 'transaction:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/transaction', name: 'add_transaction', methods: ['POST'])]
    public function addTransaction(Request $request, ValidatorInterface $validator, PartyRepository $partyRepo, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true);
        $transaction = new Transaction();
        $transaction->setCategory($data['transaction']['category']);
        $transaction->setAmount($data['transaction']['amount']);
        $transaction->setUserOwner($user->getId());
        $transaction->setTransectedAt($data['transaction']['transectedAt']);

        $partyName = $data['party']['name'];
        $request = $partyRepo->findOneBy(['name' => $partyName]);
        if ($request) {
            $party = $request;
        } else {
            $party = new Party();
            $party->setName($data['party']['name']);
        }
        $transaction->setParties($party);
        $party->addTransaction($transaction);

        $partyErrors = $validator->validate($party);
        $transactionErrors = $validator->validate($transaction);
        if (count($partyErrors) > 0 || count($transactionErrors) > 0) {
            $errors = [];
            foreach ($partyErrors as $error) {
                $errors[] = $error->getMessage();
            }
            foreach ($transactionErrors as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        $manager->persist($transaction);
        $manager->persist($party);
        $manager->flush();

        return new JsonResponse($transaction, Response::HTTP_CREATED, [], true);
    }

    #[Route('/transaction/{id}', name: 'delete_transaction', methods: ['DELETE'])]
    public function deleteTransaction(TransactionRepository $repo, EntityManagerInterface $manager,Security $security, Transaction $transaction = null): JsonResponse
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
