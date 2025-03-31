<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column]
    private ?float $maxSpend = null;

    #[ORM\Column(length: 20)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $ownerUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMaxSpend(): ?float
    {
        return $this->maxSpend;
    }

    public function setMaxSpend(float $maxSpend): static
    {
        $this->maxSpend = $maxSpend;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getOwnerUser(): ?user
    {
        return $this->ownerUser;
    }

    public function setOwnerUser(?user $ownerUser): static
    {
        $this->ownerUser = $ownerUser;

        return $this;
    }
}
