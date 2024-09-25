<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Services\ActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/activities', name: 'api_activity_')]
class ActivityController extends AbstractController
{
    private $activityService;
    
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    
    // en requirement, on peut accepte des valeurs au format 45.9276288 pour les latitudes et longitudes
    #[Route('/page/{page}/{lat}/{lng}', name: 'closest_activities', methods: ['GET'])]
    public function listClosestActivities(int $page, string $lat, string $lng): JsonResponse
    {
        $activities = $this->activityService->getClosestActivities($page, $lat, $lng);
        // dd('Hello');
        return $this->json($activities, 200, [], ['groups' => 'activity.list']);
    }
}
