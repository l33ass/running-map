<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Run;
use App\Entity\Admin;
use App\Entity\Runner;
use App\Entity\Coordinates;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $faker;

    const N_RUNNERS = 10;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadRun($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        $admin = new Admin();

        for ($i = 1; $i < self::N_RUNNERS; $i++) {
            $runner = new Runner();
            $runner
                ->setLogin($this->faker->userName())
                ->setPassword($this->passwordHasher->hashPassword($runner, '1234'))
                ->setRoles(['ROLE_RUNNER'])
                ->setPicture("image.png");
            $this->addReference("runner-" . $i, $runner);
            $manager->persist($runner);
        }

        $admin
            ->setLogin("superadmin")
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, 'root'));
        $manager->persist($admin);

        $manager->flush();
    }

    public function loadRun(ObjectManager $manager)
    {
        $run = new Run();
        $run
            ->setName("Epic Run")
            ->setCreatedAt(new \DateTimeImmutable())
            ->setMap("map.png")
            ->setRunDate(new \DateTimeImmutable());

        $this->getReference("runner-1")->addRun($run);

        $manager->persist($run);

        for ($i = 1; $i < self::N_RUNNERS; $i++) {
            $coords = new Coordinates();
            $coords
                ->setRun($run)
                ->setCoordsDate(new \DateTimeImmutable())
                ->setLatitude($this->faker->latitude())
                ->setLongitude($this->faker->longitude())
                ->setRunner($this->getReference("runner-1"));

            $manager->persist($coords);
        }
        $manager->flush();
    }
}
