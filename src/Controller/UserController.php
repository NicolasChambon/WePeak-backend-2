<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'api_user_')]
class UserController extends AbstractController
{
    #[Route('/{id}', name: 'user_detail', methods: ['GET'])]
    public function getUserDetail(User $user): JsonResponse
    {
        return $this->json($user, 200, [], ['groups' => 'user.detail']);
    }
}
