<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(UserRepository $userRepo, SerializerInterface $serializer): JsonResponse
    {
        $result = $userRepo->findUserDashboardData(5, $this->getUser());
        if (!$result) {
            return new JsonResponse('{"message": "No users found"}', Response::HTTP_NOT_FOUND, [], true);
        }
        $response = $serializer->serialize($result, 'json', ['groups' => ['transaction:read', 'user:read', 'pots:read']]);
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }
    #[Route('/complete', name: 'home_complete')]
    public function complete(UserRepository $userRepo, SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = $userRepo->findByUserAllDatas($user);
        if (!$result) {
            return new JsonResponse('{"message": "No users found"}', Response::HTTP_NOT_FOUND, [], true);
        }
        $response = $serializer->serialize($result, 'json', ['groups' => ['transaction:read', 'user:read', 'pots:read']]);
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }
}