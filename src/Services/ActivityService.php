<?php

namespace App\Services;

use App\Repository\ActivityRepository;

class ActivityService
{
    private $activityRepository;

    public function __construct(ActivityRepository $activityRepository)
    {
        $this->activityRepository = $activityRepository;
    }

    public function getClosestActivities(int $page, string $lat, string $lng): array
    {
        // Validate and transform latitude and longitude
        if ($lat === null || $lng === null) {
            throw new \InvalidArgumentException('Latitude and longitude are required');
        }
        $latDot = str_replace(',', '.', $lat);
        $lngDot = str_replace(',', '.', $lng);

        if (!is_numeric($latDot) || !is_numeric($lngDot)) {
            throw new \InvalidArgumentException('Latitude and longitude must be numeric');
        }
        $latFloat = floatval($latDot);
        $lngFloat = floatval($lngDot);

        // Get closest activities using the repository
        return $this->activityRepository->findClosestActivities($page, $latFloat, $lngFloat);
    }

}