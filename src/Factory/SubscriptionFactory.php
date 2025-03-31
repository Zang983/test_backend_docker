<?php

namespace App\Factory;

use App\Entity\Subscription;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Subscription>
 */
final class SubscriptionFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Subscription::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'amount' => self::faker()->randomFloat(2,0,3000),
            'dayOfMonth' => self::faker()->numberBetween(1, 31),
            'frequency' => self::faker()->randomElement(['Monthly', 'Yearly','Weekly','Bi-Weekly','Bi-Monthly','Quarterly']),
            'name' => self::faker()->randomElement(['Netflix', 'Spotify', 'Hulu', 'Amazon Prime', 'Disney+', 'Apple Music', 'YouTube Premium', 'HBO Max']),
            'ownerUser' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Subscription $subscription): void {})
        ;
    }
}
