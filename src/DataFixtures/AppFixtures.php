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
        $users = UserFactory::createMany(10, function () {
            return [
                'password' => $this->hasher->hashPassword(new User(), 'password'),
            ];
        });
        $parties = PartyFactory::createMany(60);


        $pots = PotsFactory::createMany(60, function () use ($users) {
            return [
                'ownerUser' => $users[array_rand($users)],
            ];
        });

        $budgets = BudgetFactory::createMany(60, function () use ($users) {
            return [
                'ownerUser' => $users[array_rand($users)],
            ];
        });

        $transactions = TransactionFactory::createMany(350, function () use ($users, $parties, $budgets) {
            $randomBudget = null;
            if (random_int(0, 1)) { // 50% de chance
                $randomBudget = $budgets[array_rand($budgets)];
            }
            return [
                'parties' => $parties[array_rand($parties)],
                'userOwner' => $users[array_rand($users)],
                'budget' => $randomBudget,
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
