<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Email;
use Error;

#[Route('/api/users', name: 'api_users_')]
class RegistrationController extends AbstractController
{
    #[Route('', name: 'register', methods: ['POST'])]
    public function register (
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
        ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = new User(); // Create a new User object
            
            // Set properties which non require any transformation
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setPseudo($data['pseudo']);
            $user->setEmail($data['email']);
            $user->setCity($data['city']);
            $user->setBirthdate(new DateTimeImmutable($data['birthdate']));
            $user->setCreatedAt(new DateTimeImmutable());
            
            // Hash the password and set it to the user object
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
    
            // Generate a token for email verification
            $verificationToken = bin2hex(random_bytes(32)); // Generate a random string of 32 characters
            $user->setVerificationToken($verificationToken); // Set the token to the user object
    
            // Save the user object to the database
            $entityManager->persist($user); // Prepare the user object to be saved
            $entityManager->flush(); // Save the user object to the database
    
            // Prepare the email to be sent
            $email = (new Email())
                ->from('nicolas.chambon.dev@gmail.com')
                ->to($user->getEmail())
                ->subject('Wepeak - Vérification de votre adresse email')
                ->text('Cliquez sur ce lien pour vérifier votre adresse email : http://localhost:8000/api/users/verify?token='.$verificationToken);
                
            $mailer->send($email); // Send the email

            // Return a JSON response
            return $this->json([
                'message' => 'User registered successfully. Please check your email to verify your account.'
            ]);
        } catch (Error $e) {
            return $this->json([
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }
}
