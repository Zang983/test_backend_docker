<?php

namespace App\Factory;

use App\Entity\Budget;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Budget>
 */
final class BudgetFactory extends PersistentProxyObjectFactory
{
    private array $categories;
    private array $colors;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(array $categories, array $colors)
    {
        $this->colors = $colors;
        $this->categories = $categories;
    }

    public static function class(): string
    {
        return Budget::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'category' => self::faker()->randomElement($this->categories),
            'color' => self::faker()->randomElement($this->colors),
            'maxSpend' => self::faker()->randomFloat(2, 0, 10000),
            'ownerUser' => null, // TODO add App\Entity\user type manually
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Budget $budget): void {})
            ;
    }
}
