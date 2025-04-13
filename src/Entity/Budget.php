<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('budget:read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('budget:read')]
    private ?string $category = null;

    #[ORM\Column]
    #[Groups('budget:read')]
    private ?float $maxSpend = null;

    #[ORM\Column(length: 35)]
    #[Groups('budget:read')]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('budget:read')]
    private ?user $ownerUser = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'budget')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups('budget:read')]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setBudget($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getBudget() === $this) {
                $transaction->setBudget(null);
            }
        }

        return $this;
    }
}