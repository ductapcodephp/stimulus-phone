<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setPhone('0987654321');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $hashedPassword = $this->passwordHasher->hashPassword($admin, '123');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('ductapcode@gmail.com');
        $user->setPhone('0123456789');
        $user->setRoles(['ROLE_USER']);
        $user->setUsername('user');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'hehehe');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
