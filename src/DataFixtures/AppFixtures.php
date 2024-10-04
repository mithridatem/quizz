<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setAvatar('https://randomuser.me/api/portraits');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        dump($user);
        $manager->persist($user);

        $adm = new User();
        $adm->setEmail('adm@test.com');
        $adm->setFirstname('John');
        $adm->setLastname('Wick');
        $adm->setAvatar('https://randomuser.me/api/portraits');
        $adm->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $adm->setPassword($this->passwordHasher->hashPassword($adm, 'password'));
        
        $manager->persist($adm);
        $manager->flush();
        dump($user,$adm);
    }
}
