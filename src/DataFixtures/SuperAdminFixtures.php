<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $superAdmin = new User();
        $superAdmin->setEmail('super.admin@bopesa.com');
        $superAdmin->setFullName('Super Admin');
        $superAdmin->setProjectName('Bopesa Marketing');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword(
            $this->passwordHasher->hashPassword(
                $superAdmin,
                'SuperAdmin123!' // Changez ce mot de passe en production
            )
        );

        $manager->persist($superAdmin);
        $manager->flush();
    }
}
