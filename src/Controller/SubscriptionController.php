<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;

final class SubscriptionController extends AbstractController
{
    #[Route('/subscription', name: 'app_subscription', methods: ['GET'])]
    public function index(Security $security, TransactionRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You are not logged in',
            ]);
        }
        $result = $repo->findBy(['userOwner' => $user->getId(), 'isRecurring' => true]);
        if (!$result) {
            return new JsonResponse(
                "No subscriptions found for this user"
                , Response::HTTP_OK, [], true);
        }
        $response = $serializer->serialize($result, 'json', ['groups' => 'transaction:read']);
        return new JsonResponse($response, Response::HTTP_OK, [], true);


    }
}
