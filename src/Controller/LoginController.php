<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api', name: 'api_')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
        ): JsonResponse 
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];

        // Check if the user exists and verify the password
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants invalides'], 401);
        }

        // Generate the token
        $token = $JWTManager->create($user);
        if (!$token) {
            return new JsonResponse(['error' => 'Erreur lors de la génération du token'], 500);
        }

        // Prepare the successfully logged user data with the token and return it
        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'thumbnail' => $user->getThumbnail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'birthdate' => $user->getBirthdate(),
            'city' => $user->getCity(),
            'description' => $user->getDescription(),
            'createdAt' => $user->getCreatedAt(),
        ];
        return new JsonResponse(['token' => $token, 'user' => $userData]);
    }
}
