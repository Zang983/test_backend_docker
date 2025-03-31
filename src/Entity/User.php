<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column]
    private ?float $balance = null;

    /**
     * @var Collection<int, transaction>
     */
    #[ORM\OneToMany(targetEntity: transaction::class, mappedBy: 'userOwner', orphanRemoval: true)]
    private Collection $transactions;

    /**
     * @var Collection<int, pots>
     */
    #[ORM\OneToMany(targetEntity: pots::class, mappedBy: 'ownerUser', orphanRemoval: true)]
    private Collection $pots;

    /**
     * @var Collection<int, subscription>
     */
    #[ORM\OneToMany(targetEntity: subscription::class, mappedBy: 'ownerUser', orphanRemoval: true)]
    private Collection $subscriptions;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'ownerUser', orphanRemoval: true)]
    private Collection $budgets;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->pots = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->budgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

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

    /**
     * @return Collection<int, transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setUserOwner($this);
        }

        return $this;
    }

    public function removeTransaction(transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getUserOwner() === $this) {
                $transaction->setUserOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, pots>
     */
    public function getPots(): Collection
    {
        return $this->pots;
    }

    public function addPot(pots $pot): static
    {
        if (!$this->pots->contains($pot)) {
            $this->pots->add($pot);
            $pot->setOwnerUser($this);
        }

        return $this;
    }

    public function removePot(pots $pot): static
    {
        if ($this->pots->removeElement($pot)) {
            // set the owning side to null (unless already changed)
            if ($pot->getOwnerUser() === $this) {
                $pot->setOwnerUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setOwnerUser($this);
        }

        return $this;
    }

    public function removeSubscription(subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getOwnerUser() === $this) {
                $subscription->setOwnerUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudget(Budget $budget): static
    {
        if (!$this->budgets->contains($budget)) {
            $this->budgets->add($budget);
            $budget->setOwnerUser($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): static
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getOwnerUser() === $this) {
                $budget->setOwnerUser(null);
            }
        }

        return $this;
    }
}
