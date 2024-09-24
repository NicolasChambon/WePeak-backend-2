<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Entity\Sport;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ){

    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadSports($manager);
    }

    public function loadUsers(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setPseudo('user' . $i)
                ->setPassword($this->hasher->hashPassword($user, 'user' . $i))
                ->setEmail('user' . $i . '@doe.fr')
                ->setFirstname('John')
                ->setLastname('Doe')
                ->setBirthdate(new \DateTimeImmutable('1980-01-01'))
                ->setCity('Cadolive')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setThumbnail('https://loremflickr.com/320/240/portrait')
                ->setIsVerified(true);
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function loadSports(ObjectManager $manager): void
    {
        $sport1 = $this->createSport($manager, 'Escalade');
        $sport2 = $this->createSport($manager, 'Randonnée');
        $sport3 = $this->createSport($manager, 'Ski de randonnée');
        $sport4 = $this->createSport($manager, 'VTT');
        $sport5 = $this->createSport($manager, 'Surf');

        $manager->flush();
    }

    public function createSport(ObjectManager $manager, string $name): Sport
    {
        $sport = new Sport();
        $sport->setName($name)
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($sport);
        return $sport;
    }

}
