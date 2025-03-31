<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups("transaction:read")]
    private ?\DateTimeImmutable $transectedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups("transaction:read")]
    private ?string $category = null;

    #[ORM\Column]
    #[Groups("transaction:read")]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userOwner = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("transaction:read")]
    private ?Party $parties = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransectedAt(): ?\DateTimeImmutable
    {
        return $this->transectedAt;
    }

    public function setTransectedAt(\DateTimeImmutable $transectedAt): static
    {
        $this->transectedAt = $transectedAt;

        return $this;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUserOwner(): ?User
    {
        return $this->userOwner;
    }

    public function setUserOwner(?User $userOwner): static
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    public function getParties(): ?Party
    {
        return $this->parties;
    }

    public function setParties(?Party $parties): static
    {
        $this->parties = $parties;

        return $this;
    }
}
