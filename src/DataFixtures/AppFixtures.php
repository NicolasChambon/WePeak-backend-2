<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Sport;
use DateTimeImmutable;
use App\Entity\Difficulty;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ){}

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadSports($manager);
        $this->loadDifficulties($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {

        $faker =  Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            
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
                ->setThumbnail('https://loremflickr.com/320/240/portrait')
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

    private array $cities = [
        'Aix-les-Bains' => ['latitude' => 45.6888, 'longitude' => 5.9150],
        'Ambérieu-en-Bugey' => ['latitude' => 45.9562, 'longitude' => 5.3574],
        'Anglet' => ['latitude' => 43.4857, 'longitude' => -1.5232],
        'Annecy' => ['latitude' => 45.8992, 'longitude' => 6.1294],
        'Annecy-le-Vieux' => ['latitude' => 45.9195, 'longitude' => 6.1438],
        'Annonay' => ['latitude' => 45.2393, 'longitude' => 4.6763],
        'Autun' => ['latitude' => 46.9496, 'longitude' => 4.2983],
        'Barberaz' => ['latitude' => 45.5736, 'longitude' => 5.9021],
        'Bayonne' => ['latitude' => 43.4933, 'longitude' => -1.4746],
        'Belley' => ['latitude' => 45.7550, 'longitude' => 5.6863],
        'Biarritz' => ['latitude' => 43.4832, 'longitude' => -1.5586],
        'Boucau' => ['latitude' => 43.5130, 'longitude' => -1.4672],
        'Bourg-en-Bresse' => ['latitude' => 46.2057, 'longitude' => 5.2258],
        'Bourgoin-Jallieu' => ['latitude' => 45.5848, 'longitude' => 5.2732],
        'Capbreton' => ['latitude' => 43.6448, 'longitude' => -1.4370],
        'Challes-les-Eaux' => ['latitude' => 45.5627, 'longitude' => 5.9718],
        'Chambéry' => ['latitude' => 45.5645, 'longitude' => 5.9175],
        'Chambéry-le-Vieux' => ['latitude' => 45.6048, 'longitude' => 5.9181],
        'Cluny' => ['latitude' => 46.4372, 'longitude' => 4.6591],
        'Combloux' => ['latitude' => 45.8925, 'longitude' => 6.6461],
        'Cordon' => ['latitude' => 45.9250, 'longitude' => 6.6199],
        'Cran-Gevrier' => ['latitude' => 45.9003, 'longitude' => 6.0982],
        'Crest' => ['latitude' => 44.7273, 'longitude' => 5.0243],
        'Crémieu' => ['latitude' => 45.7222, 'longitude' => 5.2561],
        'Croix' => ['latitude' => 50.6782, 'longitude' => 3.1455],
        'Demi-Quartier' => ['latitude' => 45.8883, 'longitude' => 6.6339],
        'Domancy' => ['latitude' => 45.8989, 'longitude' => 6.6500],
        'Epagny' => ['latitude' => 45.9339, 'longitude' => 6.0647],
        'Grenoble' => ['latitude' => 45.1885, 'longitude' => 5.7245],
        'Halluin' => ['latitude' => 50.7977, 'longitude' => 3.1333],
        'Hossegor' => ['latitude' => 43.6631, 'longitude' => -1.4370],
        'La Madeleine' => ['latitude' => 50.6513, 'longitude' => 3.0664],
        'La Ravoire' => ['latitude' => 45.5699, 'longitude' => 5.9344],
        'Labenne' => ['latitude' => 43.5924, 'longitude' => -1.4509],
        'Lambersart' => ['latitude' => 50.6515, 'longitude' => 3.0178],
        'Le Bourget-du-Lac' => ['latitude' => 45.6539, 'longitude' => 5.8507],
        'Le Puy-en-Velay' => ['latitude' => 45.0419, 'longitude' => 3.8831],
        'Les Houches' => ['latitude' => 45.8934, 'longitude' => 6.7914],
        'Lille' => ['latitude' => 50.6293, 'longitude' => 3.0573],
        'Mâcon' => ['latitude' => 46.3069, 'longitude' => 4.8283],
        'Marcq-en-Barœul' => ['latitude' => 50.6728, 'longitude' => 3.0977],
        'Megève' => ['latitude' => 45.8576, 'longitude' => 6.6153],
        'Meythet' => ['latitude' => 45.9183, 'longitude' => 6.1023],
        'Metz-Tessy' => ['latitude' => 45.9313, 'longitude' => 6.1081],
        'Montélimar' => ['latitude' => 44.5588, 'longitude' => 4.7495],
        'Montmélian' => ['latitude' => 45.4870, 'longitude' => 5.9204],
        'Nyons' => ['latitude' => 44.3592, 'longitude' => 5.1426],
        'Ondres' => ['latitude' => 43.5595, 'longitude' => -1.4346],
        'Passy' => ['latitude' => 45.9361, 'longitude' => 6.7006],
        'Péage-de-Roussillon' => ['latitude' => 45.3778, 'longitude' => 4.7688],
        'Pérouges' => ['latitude' => 45.9030, 'longitude' => 5.1772],
        'Poisy' => ['latitude' => 45.9192, 'longitude' => 6.0514],
        'Pont-de-Vaux' => ['latitude' => 46.4183, 'longitude' => 4.9296],
        'Pringy' => ['latitude' => 45.9467, 'longitude' => 6.1261],
        'Quintal' => ['latitude' => 45.8569, 'longitude' => 6.0699],
        'Roanne' => ['latitude' => 46.0368, 'longitude' => 4.0711],
        'Romans-sur-Isère' => ['latitude' => 45.0460, 'longitude' => 5.0538],
        'Roubaix' => ['latitude' => 50.6916, 'longitude' => 3.1746],
        'Saint-Alban-Leysse' => ['latitude' => 45.5606, 'longitude' => 5.9539],
        'Saint-Étienne' => ['latitude' => 45.4397, 'longitude' => 4.3872],
        'Saint-Gervais-les-Bains' => ['latitude' => 45.8919, 'longitude' => 6.7018],
        'Sallanches' => ['latitude' =>  45.9228, 'longitude' => 6.7015],
        'Seignosse' => ['latitude' => 43.6923, 'longitude' => -1.3984],
        'Seynod' => ['latitude' => 45.8796, 'longitude' => 6.0948],
        'Tarare' => ['latitude' => 45.8947, 'longitude' => 4.4322],
        'Tarnos' => ['latitude' => 43.5397, 'longitude' => -1.4641],
        'Tourcoing' => ['latitude' => 50.7235, 'longitude' => 3.1615],
        'Tournus' => ['latitude' => 46.5703, 'longitude' => 4.9125],
        'Trévoux' => ['latitude' => 45.9336, 'longitude' => 4.7661],
        'Valence' => ['latitude' => 44.9334, 'longitude' => 4.8929],
        'Veyrier-du-Lac' => ['latitude' => 45.8828, 'longitude' => 6.1611],
        'Vienne' => ['latitude' => 45.5300, 'longitude' => 4.8786],
        'Villefranche-sur-Mer' => ['latitude' => 43.7034, 'longitude' => 7.3052],
        'Villefranche-sur-Saône' => ['latitude' => 45.9884, 'longitude' => 4.7216],
        'Wambrechies' => ['latitude' => 50.6839, 'longitude' => 3.0315],
        'Wasquehal' => ['latitude' => 50.6677, 'longitude' => 3.1234],
    ];
}
