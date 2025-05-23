<?php

namespace App\Factory;

use App\Entity\Transaction;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Transaction>
 */
final class TransactionFactory extends PersistentProxyObjectFactory
{
    private $categories = [];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    public static function class(): string
    {
        return Transaction::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {

        return [
            'amount' => self::faker()->randomFloat(2, -10000, 10000),
            'category' => self::faker()->randomElement($this->categories),
            'parties' => null, // TODO add App\Entity\party type manually
            'transectedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'userOwner' => UserFactory::new(),
            'isRecurring' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Transaction $transaction): void {})
            ;
    }
}
