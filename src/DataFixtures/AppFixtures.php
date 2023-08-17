<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Entity\MicroPost;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $user1 = new User();
        $user1->setEmail('test1@op.pl');
        $user1->setPassword(
            $this->userPasswordHasherInterface->hashPassword($user1, 'haslo1234')
        );
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('test2@op.pl');
        $user2->setPassword(
            $this->userPasswordHasherInterface->hashPassword($user2, 'haslo1234')
        );
        $manager->persist($user2);

        $microPost1 = new MicroPost();
        $microPost1->setTitle('Welcome to Poland!');
        $microPost1->setText('Welcome to Poland!');
        $microPost1->setCreated(new DateTime());
        $microPost1->setAuthor($user1); //po dodaniu relacji pomiędzy użytkownikami a postami i komentarzami
        $manager->persist($microPost1);

        $microPost2 = new MicroPost();
        $microPost2->setTitle('Welcome to Russia!');
        $microPost2->setText('Welcome to Russia!');
        $microPost2->setCreated(new DateTime());
        $microPost2->setAuthor($user1); //po dodaniu relacji pomiędzy użytkownikami a postami i komentarzami
        $manager->persist($microPost2);

        $microPost3 = new MicroPost();
        $microPost3->setTitle('Welcome to USA!');
        $microPost3->setText('Welcome to USA!');
        $microPost3->setCreated(new DateTime());
        $microPost3->setAuthor($user2); //po dodaniu relacji pomiędzy użytkownikami a postami i komentarzami
        $manager->persist($microPost3);

        $manager->flush();
    }
}
