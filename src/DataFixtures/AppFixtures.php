<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Sport;
use DateTimeImmutable;
use App\Entity\Activity;
use App\Entity\Pictures;
use App\Entity\Difficulty;
use App\Entity\Participation;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private SluggerInterface $slugger;
    private int $nbActivities = 100; // 100 activities
    private int $nbUsers = 300; // 300 users

    public function __construct(
        UserPasswordHasherInterface $hasher,
        SluggerInterface $slugger
        )
    {
        $this->hasher = $hasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager, $this->hasher);
        $this->loadSports($manager);
        $this->loadDifficulties($manager);
        $this->createAndLoadActivities($manager);
        $this->createAndLoadParticipations($manager);
        $this->createAndLoadPictures($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {

        $faker =  Factory::create('fr_FR');

        for ($i = 0; $i < $this->nbUsers; $i++) {
            
            $firstName = $faker->firstName();
            $lastName = str_replace(' ', '', $faker->lastName());
            $firstNameTranslit = iconv('UTF-8', 'ASCII//TRANSLIT', $firstName);
            $lastNameTranslit = iconv('UTF-8', 'ASCII//TRANSLIT', $lastName);

            $pseudo = strtolower(substr($firstNameTranslit, 0, 3)) . '.' . strtolower(str_replace(' ', '', $lastNameTranslit)) . $faker->numberBetween(0, 99);
            $email = $pseudo . '@' . $faker->freeEmailDomain();

            $user = new User();
            $user->setPseudo($pseudo)
                ->setEmail($email)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setPassword($this->hasher->hashPassword($user, $pseudo))
                ->setBirthdate(new DateTimeImmutable($faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d')))
                ->setCity(array_rand($this->cities))
                ->setCreatedAt(new DateTimeImmutable())
                ->setThumbnail('https://loremflickr.com/320/240/people,face?random='.$faker->numberBetween(1, 100))
                ->setIsVerified(true);
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function loadSports(ObjectManager $manager): void
    {
        $this->createSport($manager, 'Escalade');
        $this->createSport($manager, 'Randonnée');
        $this->createSport($manager, 'Ski de randonnée');
        $this->createSport($manager, 'VTT');
        $this->createSport($manager, 'Surf');

        $manager->flush();
    }

    private function createSport(ObjectManager $manager, string $name): void
    {
        $sport = new Sport();
        $sport->setName($name)
            ->setCreatedAt(new DateTimeImmutable());
        $manager->persist($sport);
    }

    private function loadDifficulties(ObjectManager $manager): void
    {
        $climbingDifficulties = [
            '4a' => '4a',
            '4b' => '4b',
            '4c' => '4c',
            '5a' => '5a',
            '5b' => '5b',
            '5c' => '5c',
            '6a' => '6a',
            '6b' => '6b',
            '6c' => '6c',
            '7a' => '7a',
            '7b' => '7b',
            '7c' => '7c',
            '8a' => '8a',
            '8b' => '8b',
            '8c' => '8c',
            '9a' => '9a',
            '9b' => '9b',
            '9c' => '9c'
        ];
        $climbing = $manager->getRepository(Sport::class)->findOneBy(['name' => 'Escalade']);
        $this->createDifficultiesForOneSport($manager, $climbing, $climbingDifficulties);
        
        $skiDifficulties = [
            'F' => 'Facile',
            'PD' => 'Peu Difficile',
            'AD' => 'Assez Difficile',
            'D' => 'Difficile',
            'TD' => 'Très Difficile',
            'ED' => 'Extrêmement Difficile',
            'Abo' => 'Abominable'
        ];
        $ski = $manager->getRepository(Sport::class)->findOneBy(['name' => 'Ski de randonnée']);
        $this->createDifficultiesForOneSport($manager, $ski, $skiDifficulties);

        $hikingDifficulties = [
            'T1' => 'Randonnée',
            'T2' => 'Randonnée en montagne',
            'T3' => 'Randonnée en montagne exigeante',
            'T4' => 'Randonnée alpine',
            'T5' => 'Randonnée alpine exigeante',
            'T6' => 'Randonnée alpine extrême'
        ];
        $hiking = $manager->getRepository(Sport::class)->findOneBy(['name' => 'Randonnée']);
        $this->createDifficultiesForOneSport($manager, $hiking, $hikingDifficulties);

        $vttDifficulties = [
            'S0' => 'Très facile',
            'S1' => 'Facile',
            'S2' => 'Peu difficile',
            'S3' => 'Difficile',
            'S4' => 'Très difficile',
            'S5' => 'Extrêmement difficile'
        ];
        $vtt = $manager->getRepository(Sport::class)->findOneBy(['name' => 'VTT']);
        $this->createDifficultiesForOneSport($manager, $vtt, $vttDifficulties);

        $surfDifficulties = [
            '1' => 'Débutant',
            '2' => 'Intermédiaire',
            '3' => 'Avancé',
            '4' => 'Expert'
        ];
        $surf = $manager->getRepository(Sport::class)->findOneBy(['name' => 'Surf']);
        $this->createDifficultiesForOneSport($manager, $surf, $surfDifficulties);

        $manager->flush();
    }

    private function createDifficultiesForOneSport(ObjectManager $manager, Sport $sport, array $difficulties): void
    {
        foreach ($difficulties as $difficultyLabel => $difficultyValue) {
            $difficulty = new Difficulty();
            $difficulty->setValue($difficultyValue)
                ->setSport($sport)
                ->setLabel($difficultyLabel)
                ->setCreatedAt(new DateTimeImmutable());
            $manager->persist($difficulty);
        }
    }

    private function createAndLoadActivities(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $users = $manager->getRepository(User::class)->findAll();
        $sports = $manager->getRepository(Sport::class)->findAll();

        for ($i = 0; $i < $this->nbActivities; $i++) {
            $city = array_rand($this->cities);
            $lat = $this->cities[$city]['latitude'];
            $lng = $this->cities[$city]['longitude'];

            $sport = $faker->randomElement($sports);
            $difficulties = $manager->getRepository(Difficulty::class)->findBy(['sport' => $sport]);
            $difficulty = $faker->randomElement($difficulties);

            $activity = new Activity();
            $activity->setName($faker->sentence(6))
                ->setDescription($faker->paragraph(3))
                ->setDate($faker->dateTimeBetween('-1 month', '+1 month'))
                ->setGroupSize($faker->numberBetween(2, 10))
                ->setCity($city)
                ->setLat($lat)
                ->setLng($lng)
                ->setCreatedAt(new DateTimeImmutable())
                ->setCreatedBy($faker->randomElement($users))
                ->setSport($sport)
                ->setDifficulty($difficulty)
                ->setSlug($this->slugger->slug($activity->getName())->lower())
                ->setThumbnail('https://loremflickr.com/320/240/mountain?random='.$faker->numberBetween(1, 100));
            
            $manager->persist($activity);
        }
        $manager->flush();
    }

    private function createAndLoadParticipations(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $activities = $manager->getRepository(Activity::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($activities as $activity) {
            $groupSize = $activity->getGroupSize();
            $numParticipants = $faker->numberBetween(0, $groupSize);
            $participants = $faker->randomElements($users, $numParticipants);

            foreach ($participants as $participant) {
                $isParticipating = $manager->getRepository(Participation::class)->findOneBy(['user' => $participant, 'activity' => $activity]);

                if ($isParticipating === null) {
                    $participation = new Participation();
                    $participation->setUser($participant)
                        ->setActivity($activity)
                        ->setStatus($faker->numberBetween(0, 1))
                        ->setCreatedAt(new DateTimeImmutable());
                    $manager->persist($participation);
                }
            }
        }
        $manager->flush();
    }

    private function createAndLoadPictures(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $activities = $manager->getRepository(Activity::class)->findAll();

        foreach ($activities as $activity) {
            $numPictures = $faker->numberBetween(2, 5);

            for ($i = 0; $i < $numPictures; $i++) {
                $picture = new Pictures();
                $picture->setLink('https://loremflickr.com/640/480/mountain?random='.$faker->numberBetween(1, 100))
                    ->setActivity($activity)
                    ->setCreatedAt(new DateTimeImmutable());
                $manager->persist($picture);
            }
        }
        $manager->flush();
    }

    private array $cities = [
        'Albertville' => ['latitude' => 45.6758, 'longitude' => 6.3901],
        'Allonzier-la-Caille' => ['latitude' => 45.9750, 'longitude' => 6.1206],
        'Amancy' => ['latitude' => 46.0733, 'longitude' => 6.3339],
        'Annecy' => ['latitude' => 45.8992, 'longitude' => 6.1294],
        'Annecy-le-Vieux' => ['latitude' => 45.9195, 'longitude' => 6.1438],
        'Annemasse' => ['latitude' => 46.1957, 'longitude' => 6.2366],
        'Anthy-sur-Léman' => ['latitude' => 46.3533, 'longitude' => 6.4275],
        'Argonay' => ['latitude' => 45.9380, 'longitude' => 6.1210],
        'Arthaz-Pont-Notre-Dame' => ['latitude' => 46.1622, 'longitude' => 6.3017],
        'Autun' => ['latitude' => 46.9496, 'longitude' => 4.2983],
        'Beaumont' => ['latitude' => 46.1424, 'longitude' => 6.1375],
        'Bonne' => ['latitude' => 46.1984, 'longitude' => 6.3039],
        'Bonneville' => ['latitude' => 46.0789, 'longitude' => 6.4083],
        'Chamonix-Mont-Blanc' => ['latitude' => 45.9237, 'longitude' => 6.8694],
        'Cran-Gevrier' => ['latitude' => 45.9091, 'longitude' => 6.1068],
        'Cranves-Sales' => ['latitude' => 46.1871, 'longitude' => 6.2996],
        'Domancy' => ['latitude' => 45.9181, 'longitude' => 6.6385],
        'Doussard' => ['latitude' => 45.7771, 'longitude' => 6.2242],
        'Duingt' => ['latitude' => 45.8310, 'longitude' => 6.2054],
        'Épagny' => ['latitude' => 45.9384, 'longitude' => 6.0859],
        'Évian-les-Bains' => ['latitude' => 46.4024, 'longitude' => 6.5932],
        'Faverges' => ['latitude' => 45.7458, 'longitude' => 6.2924],
        'Gaillard' => ['latitude' => 46.1884, 'longitude' => 6.2172],
        'Giez' => ['latitude' => 45.7466, 'longitude' => 6.2383],
        'Groisy' => ['latitude' => 46.0019, 'longitude' => 6.1536],
        'La Balme-de-Sillingy' => ['latitude' => 45.9644, 'longitude' => 6.0247],
        'La Roche-sur-Foron' => ['latitude' => 46.0667, 'longitude' => 6.3146],
        'Le Grand-Bornand' => ['latitude' => 45.9429, 'longitude' => 6.4278],
        'Les Contamines-Montjoie' => ['latitude' => 45.8236, 'longitude' => 6.7277],
        'Les Houches' => ['latitude' => 45.8883, 'longitude' => 6.7982],
        'Marnaz' => ['latitude' => 46.0637, 'longitude' => 6.5154],
        'Marcellaz' => ['latitude' => 46.1197, 'longitude' => 6.4215],
        'Megève' => ['latitude' => 45.8572, 'longitude' => 6.6141],
        'Menthon-Saint-Bernard' => ['latitude' => 45.8457, 'longitude' => 6.2016],
        'Meythet' => ['latitude' => 45.9154, 'longitude' => 6.0986],
        'Morillon' => ['latitude' => 46.0825, 'longitude' => 6.6739],
        'Passy' => ['latitude' => 45.9249, 'longitude' => 6.7055],
        'Praz-sur-Arly' => ['latitude' => 45.8323, 'longitude' => 6.5726],
        'Reignier-Ésery' => ['latitude' => 46.1375, 'longitude' => 6.2539],
        'Rumilly' => ['latitude' => 45.8527, 'longitude' => 5.9478],
        'Saint-Gervais-les-Bains' => ['latitude' => 45.8956, 'longitude' => 6.7104],
        'Saint-Jorioz' => ['latitude' => 45.8297, 'longitude' => 6.1576],
        'Saint-Jeoire' => ['latitude' => 46.1447, 'longitude' => 6.4736],
        'Saint-Martin-Bellevue' => ['latitude' => 45.9561, 'longitude' => 6.1377],
        'Saint-Pierre-en-Faucigny' => ['latitude' => 46.0647, 'longitude' => 6.4321],
        'Sallanches' => ['latitude' => 45.9450, 'longitude' => 6.6318],
        'Samoëns' => ['latitude' => 46.0846, 'longitude' => 6.7281],
        'Seynod' => ['latitude' => 45.8844, 'longitude' => 6.0874],
        'Talloires' => ['latitude' => 45.8375, 'longitude' => 6.2143],
        'Taninges' => ['latitude' => 46.1319, 'longitude' => 6.5946],
        'Thônes' => ['latitude' => 45.8814, 'longitude' => 6.3197],
        'Thonon-les-Bains' => ['latitude' => 46.3747, 'longitude' => 6.4758],
        'Ugine' => ['latitude' => 45.7494, 'longitude' => 6.4247],
        'Vallières-sur-Fier' => ['latitude' => 45.9007, 'longitude' => 5.9656],
        'Veyrier-du-Lac' => ['latitude' => 45.8769, 'longitude' => 6.1658],
        'Ville-la-Grand' => ['latitude' => 46.2039, 'longitude' => 6.2564],
        'Villy-le-Pelloux' => ['latitude' => 45.9687, 'longitude' => 6.1173],
        'Aime-la-Plagne' => ['latitude' => 45.5558, 'longitude' => 6.6546],
        'Amancy' => ['latitude' => 46.0733, 'longitude' => 6.3339],
        'Arâches-la-Frasse' => ['latitude' => 46.0268, 'longitude' => 6.6482],
        'Ayse' => ['latitude' => 46.0796, 'longitude' => 6.4537],
        'Beaufort' => ['latitude' => 45.7226, 'longitude' => 6.5696],
        'Boëge' => ['latitude' => 46.2122, 'longitude' => 6.4665],
        'Cervens' => ['latitude' => 46.3156, 'longitude' => 6.4823],
        'Cluses' => ['latitude' => 46.0627, 'longitude' => 6.5755],
        'Combloux' => ['latitude' => 45.8992, 'longitude' => 6.6241],
        'Dingy-Saint-Clair' => ['latitude' => 45.9340, 'longitude' => 6.2733],
        'Entremont' => ['latitude' => 45.9361, 'longitude' => 6.3244],
        'Flumet' => ['latitude' => 45.8196, 'longitude' => 6.5279],
        'La Clusaz' => ['latitude' => 45.9058, 'longitude' => 6.4278],
        'La Tour' => ['latitude' => 46.1781, 'longitude' => 6.4114],
        'Les Gets' => ['latitude' => 46.1578, 'longitude' => 6.6677],
        'Magland' => ['latitude' => 46.0173, 'longitude' => 6.6313],
        'Manigod' => ['latitude' => 45.8583, 'longitude' => 6.3725],
        'Mieussy' => ['latitude' => 46.1370, 'longitude' => 6.5251],
        'Mont-Saxonnex' => ['latitude' => 46.0457, 'longitude' => 6.4975],
        'Morillon' => ['latitude' => 46.0825, 'longitude' => 6.6739],
        'Nancy-sur-Cluses' => ['latitude' => 46.0279, 'longitude' => 6.6151],
        'Praz-sur-Arly' => ['latitude' => 45.8323, 'longitude' => 6.5726],
        'Saint-Gervais-les-Bains' => ['latitude' => 45.8956, 'longitude' => 6.7104],
        'Saint-Jeoire' => ['latitude' => 46.1447, 'longitude' => 6.4736],
        'Scionzier' => ['latitude' => 46.0605, 'longitude' => 6.5298],
        'Serraval' => ['latitude' => 45.8336, 'longitude' => 6.3222],
        'Sixt-Fer-à-Cheval' => ['latitude' => 46.0656, 'longitude' => 6.7787],
        'Verchaix' => ['latitude' => 46.0926, 'longitude' => 6.7266],
        'Viuz-en-Sallaz' => ['latitude' => 46.1454, 'longitude' => 6.4151],
        'Vougy' => ['latitude' => 46.0565, 'longitude' => 6.5111],
    ];
}
