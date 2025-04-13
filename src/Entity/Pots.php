<?php

namespace App\Entity;

use App\Repository\PotsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PotsRepository::class)]
class Pots
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pots:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pots:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['pots:read'])]
    private ?float $balance = null;

    #[ORM\Column]
    #[Groups(['pots:read'])]
    private ?float $target = null;

    #[ORM\ManyToOne(inversedBy: 'pots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ownerUser = null;

    #[ORM\Column(length: 35)]
    #[Groups(['pots:read'])]
    private ?string $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTarget(): ?float
    {
        return $this->target;
    }

    public function setTarget(float $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getOwnerUser(): ?User
    {
        return $this->ownerUser;
    }

    public function setOwnerUser(?User $ownerUser): static
    {
        $this->ownerUser = $ownerUser;

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
}