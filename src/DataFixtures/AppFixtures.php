<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $users = UserFactory::createMany(20, function () {
            return [
                'password' => $this->hasher->hashPassword(new User(), 'password'),
            ];
        });
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
