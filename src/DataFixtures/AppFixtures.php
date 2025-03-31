<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Entity\User;
use App\Factory\BudgetFactory;
use App\Factory\PartyFactory;
use App\Factory\PotsFactory;
use App\Factory\SubscriptionFactory;
use App\Factory\TransactionFactory;
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
        $users = UserFactory::createMany(50, function () {
            return [
                'password' => $this->hasher->hashPassword(new User(), 'password'),
            ];
        });
        $parties = PartyFactory::createMany(60);

        $budgets = BudgetFactory::createMany(200, function () use ($users) {
            return [
                'ownerUser' => $users[array_rand($users)],
            ];
        });
        $pots = PotsFactory::createMany(200, function () use ($users) {
            return [
                'ownerUser' => $users[array_rand($users)],
            ];
        });

        $transactions = TransactionFactory::createMany(250, function () use ($users, $parties) {
            return [
                'parties' => $parties[array_rand($parties)],
                'userOwner' => $users[array_rand($users)],
            ];
        });

        $subscriptions = SubscriptionFactory::createMany(200, function () use ($users) {
            return [
                'ownerUser' => $users[array_rand($users)],
            ];
        });
        $manager->flush();
    }
}
