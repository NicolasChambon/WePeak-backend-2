<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Services\ActivityService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/activities', name: 'api_activity_')]
class ActivityController extends AbstractController
{
    private ActivityService $activityService;
    
    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    
    #[Route('/page/{page}/{lat}/{lng}', name: 'closest_activities', methods: ['GET'])]
    public function listClosestActivities(int $page, string $lat, string $lng): JsonResponse
    {
        $activities = $this->activityService->getClosestActivities($page, $lat, $lng);

        return $this->json($activities, 200, [], ['groups' => 'activity.list']);
    }

    #[Route('/{id}', name: 'activity_detail', methods: ['GET'])]
    public function getActivityDetail(Activity $activity): JsonResponse
    {
        return $this->json($activity, 200, [], ['groups' => 'activity.detail']);
    }
}
