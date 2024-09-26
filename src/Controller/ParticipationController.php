<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Activity;
use App\Entity\Participation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/participations', name: 'api_participation_')] 
class ParticipationController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'add_participation', methods: ['POST'])]
    public function addParticipation(Request $request): JsonResponse
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        $userId = $data['userId'];
        $activityId = $data['activityId'];

        // Check if the user and the activity exist
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $activity = $this->entityManager->getRepository(Activity::class)->find($activityId);
        if (!$user || !$activity) {
            return new JsonResponse(['error' => 'User or activity not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the user is already participating in the activity
        if ($activity->getParticipations()->contains($user)) {
            return new JsonResponse(['error' => 'User is already participating in the activity'], Response::HTTP_CONFLICT);
        }

        $participation = new Participation();
        $participation->setUser($user)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setActivity($activity);

        $this->entityManager->persist($participation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Participation added'], 201);
    }

    #[Route('/{id}', name: 'delete_participation', methods: ['DELETE'])]
    public function deleteParticipation(Participation $participation): JsonResponse
    {
        $this->entityManager->remove($participation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Participation deleted'], 200);
    }
}
