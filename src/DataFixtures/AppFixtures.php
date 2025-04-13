<?php

namespace App\DataFixtures;

use Faker\Generator;
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
    private Generator $faker;

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        $this->faker = new Generator();
    }

    public function load(ObjectManager $manager): void
    {
        $firstUser = UserFactory::createOne(function () {
            return [
                'email' => 'test@test.test',
                'password' => $this->hasher->hashPassword(new User(), 'password')
            ];
        });
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
        $budgetsFirstUser = BudgetFactory::createMany(8, function () use ($firstUser) {
            return [
                'ownerUser' => $firstUser,
            ];
        });

        $firstUserTransactions = TransactionFactory::createMany(422, function () use ($firstUser, $parties, $budgetsFirstUser) {
            $randomBudget = null;
            $amount = $this->faker->randomFloat(2, -10000, 10000);
            if ($amount < 0) {
                if (random_int(0, 1)) { // 50% de chance
                    $randomBudget = $budgetsFirstUser[array_rand($budgetsFirstUser)];
                }
            }
            return [
                'amount' => $amount,
                'parties' => $parties[array_rand($parties)],
                'userOwner' => $firstUser,
                'budget' => $randomBudget,
            ];
        });

        $transactions = TransactionFactory::createMany(200, function () use ($users, $parties, $budgets) {
            $randomBudget = null;
            $amount = $this->faker->randomFloat(2, -10000, 10000);
            if ($amount < 0) {
                if (random_int(0, 1)) { // 50% de chance
                    $randomBudget = $budgets[array_rand($budgets)];
                }
            }
            return [
                'amount' => $amount,
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
