<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestUserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer l'utilisateur 1
        $user1 = new Client();
        $user1->setEmail('user1@test.com');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password'));
        $user1->setRoles(['ROLE_USER']);
        $user1->setFullName('User 1');
        $manager->persist($user1);

        // Créer l'utilisateur 2
        $user2 = new Client();
        $user2->setEmail('user2@test.com');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password'));
        $user2->setRoles(['ROLE_USER']);
        $user2->setFullName('User 2');
        $manager->persist($user2);

        // Créer l'administrateur
        $admin = new Client();
        $admin->setEmail('admin@test.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setFullName('Admin');
        $manager->persist($admin);

        $manager->flush();
    }
}
