<?php

namespace App\Factory;

use App\Entity\Pots;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Pots>
 */
final class PotsFactory extends PersistentProxyObjectFactory
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
        return Pots::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'balance' => self::faker()->randomFloat(2, 0, 10000),
            'color' => self::faker()->colorName(),
            'name' => self::faker()->userName(),
            'ownerUser' => UserFactory::new(),
            'target' => self::faker()->randomFloat(2,0,100000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Pots $pots): void {})
        ;
    }
}
