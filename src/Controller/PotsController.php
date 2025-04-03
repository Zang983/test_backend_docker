<?php

namespace App\Controller;

use App\Entity\Pots;
use App\Entity\User;
use App\Repository\PotsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PotsController extends AbstractController
{
    #[Route('/pots', name: 'app_pots', methods: ['GET'])]
    public function index(Security $security, PotsRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $user = $security->getUser();
        $pots = $repo->findBy(['ownerUser' => $user->getId()]);
        $response = $serializer->serialize(
            $pots,
            'json',
            ['groups' => 'pots:read']
        );
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }

    #[Route('/pots', name: 'create_pots', methods: ['POST'])]
    public function createPots(Security $security, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
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
        $pot = new Pots();
        $pot->setName($data['name']);
        $pot->setBalance($data['balance']);
        $pot->setTarget($data['target']);
        $pot->setOwnerUser($user);
        $pot->setColor($data['color']);
        $errors = $validator->validate($pot);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $errorsString,
            ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $manager->persist($pot);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating pot',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'message' => 'Pot created successfully',
        ], Response::HTTP_CREATED);
    }

    #[Route('/pots/{id}', name: 'update_pots', methods: ['PUT'])]
    function editPot(Security $security, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator, Pots $pot = null): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        if (!$pot) {
            return $this->json([
                'message' => 'Pot not found',
            ]);
        }
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json([
                'message' => 'No data found',
            ]);
        }
        $pot->setName($data['name'] ?? $pot->getName())
            ->setBalance($data['balance'] ?? $pot->getBalance())
            ->setTarget($data['target'] ?? $pot->getTarget())
            ->setColor($data['color'] ?? $pot->getColor());

        $errors = $validator->validate($pot);
        if (count($errors) > 0) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => (string)$errors,
            ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $manager->persist($pot);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating pot',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'message' => 'Pot updated successfully',
        ], Response::HTTP_OK);
    }
    #[Route('/pots/{id}', name: 'delete_pots', methods: ['DELETE'])]
    function deletePot(Security $security, EntityManagerInterface $manager, Pots $pot = null) : JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        if (!$pot) {
            return $this->json([
                'message' => 'Pot not found',
            ]);
        }
        try {
            $manager->remove($pot);
            $manager->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting pot',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'message' => 'Pot deleted successfully',
        ], Response::HTTP_OK);
    }
}
