<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_')]
class RegistrationController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'register', methods: ['POST'])]
    public function register (
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
        ): JsonResponse
    {
        // Here we use a try/catch block to handle potential errors in email 
        // sending or user creation
        try {
            $data = json_decode($request->getContent(), true);

            // Check if one required data is missing
            if (!isset($data['firstname']) || !isset($data['lastname']) || !isset($data['pseudo']) || !isset($data['email']) || !isset($data['city']) || !isset($data['birthdate']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Missing required data.'
                ], 400);
            }

            // Validate the email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'message' => 'Invalid email.'
                ], 400);
            }

            // Check if the email is already used
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $data['email']
            ]);
            if ($user) {
                return $this->json([
                    'message' => 'Email already used.'
                ], 400);
            }

            // and more accurate checks can be added here...

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
            $this->entityManager->persist($user); // Prepare the user object to be saved
            $this->entityManager->flush(); // Save the user object to the database
    
            // Prepare the email to be sent
            $email = (new Email())
                ->from('nicolas.chambon.dev@gmail.com')
                ->to($user->getEmail())
                ->subject('Wepeak - Vérification de votre adresse email')
                ->text('Cliquez sur ce lien pour vérifier votre adresse email : http://localhost:8000/api/users/verify/'.$verificationToken);
                
            $mailer->send($email); // Send the email
            // if email is not sent, try the following command in the terminal: 
            // php bin/console messenger:consume async --time-limit=3600

            // Return a JSON response
            return $this->json([
                'message' => 'User registered successfully. Please check your email to verify your account.'
            ]);
        } catch (Error $e) {
            return $this->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/verify/{token}', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail (
        string $token,
        ): RedirectResponse
    {
        try {
            // Find the user by the verification token
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'verificationToken' => $token
            ]);
            
            // If the user is not found, return a 404 error
            if (!$user) {
                return $this->json([
                    'message' => 'User not found.'
                ], 404);
            }
    
            // Set the user as verified and remove the verification token
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $this->entityManager->flush();
            
            return $this->redirect('http://localhost:5173/login/first-time');
        } catch (Error $e) {
            return $this->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
