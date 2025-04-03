<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\User;
use App\Repository\BudgetRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


final class BudgetController extends AbstractController
{
    #[Route('/budget', name: 'app_budget', methods: ['GET'])]
    public function index(BudgetRepository $repo, Security $security, SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        $result = $repo->findBy(['ownerUser' => $user->getId()]);
        if (!$result) {
            return $this->json([
                'message' => 'No budgets found for this user',
            ]);
        }
        $response = $serializer->serialize($result, 'json', ['groups' => 'budget:read']);
        if (!$response) {
            return $this->json([
                'message' => 'No budgets found for this user',
            ]);
        }
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }

    #[Route('/budget/', name: 'create_budget', methods: ['POST'])]
    public function createBudget(Security $security, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json([
                'message' => 'No data found',
            ]);
        }
        $budget = new Budget();
        $budget->setCategory($data['category']);
        $budget->setMaxSpend($data['amount']);
        $budget->setOwnerUser($user);
        $budget->setColor($data['color']);
        $errors = $validator->validate($budget);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        try {
            $manager->persist($budget);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating budget', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('', Response::HTTP_OK, [], true);
    }

    #[Route('/budget/{id}', name: 'edit_budget', methods: ['PUT'])]
    public function editBudget(Security $security, EntityManagerInterface $manager, Request $request, ValidatorInterface $validator, Budget $budget = null): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        if (!$budget) {
            return $this->json([
                'message' => 'Budget not found',
            ]);
        }
        if ($budget->getOwnerUser() !== $user) {
            return $this->json([
                'message' => 'You are not the owner of this budget',
            ]);
        }
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json([
                'message' => 'No data found',
            ]);
        }
        $budget->setCategory($data['category'] ?? $budget->getCategory())
            ->setMaxSpend($data['maxSpend'] ?? $budget->getMaxSpend())
            ->setColor($data['color'] ?? $budget->getColor());

        $errors = $validator->validate($budget);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        try {
            $manager->persist($budget);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating budget', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('', Response::HTTP_OK, [], true);
    }

    #[Route('/budget/{id}', name: 'delete_budget', methods: ['DELETE'])]
    public function deleteBudget(Security $security, EntityManagerInterface $manager, Budget $budget = null): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        if (!$budget) {
            return $this->json([
                'message' => 'Budget not found',
            ]);
        }
        if ($budget->getOwnerUser() !== $user) {
            return $this->json([
                'message' => 'You are not the owner of this budget',
            ]);
        }
        try {
            $manager->remove($budget);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting budget', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('', Response::HTTP_OK, [], true);
    }

}
