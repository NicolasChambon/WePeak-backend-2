<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActivityRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function findClosestActivities(int $page, float $lat, float $lng): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT
                a,
                ( 6371 * acos(
                    cos(radians(:lat)) * cos(radians(a.lat )) *
                    cos(radians(a.lng) - radians(:lng)) + sin(radians(:lat)) *
                    sin(radians( a.lat) 
                    ) 
                )) AS distance
            FROM App\Entity\Activity a
            ORDER BY distance ASC'
        );

        $query->setParameter('lat', $lat);
        $query->setParameter('lng', $lng);
        $query->setMaxResults(10); // The 10 closest activities

        return $query->getResult();
    }
}
