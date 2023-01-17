<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\AccountRepository;
use App\State\AccountCollectionProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [new Get(), new Patch(), new GetCollection(provider: AccountCollectionProvider::class), new Post()])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['email' => 'exact'])]
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[UniqueEntity(fields: ['email'])]
class Account
{
    public const LIFETIME = '3 months';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'App\Doctrine\ORM\HashidsIdGenerator')]
    #[ORM\Column(type: 'string')]
    #[ApiProperty('Account ID Code', true, false)]
    private ?string $id = null;

    #[ORM\Column(type: 'datetime')]
    #[ApiProperty('Account creation date and time', true, false)]
    private ?\DateTimeInterface $creationDate;

    #[ORM\Column(type: 'datetime')]
    #[ApiProperty('Account expiration date and time')]
    private ?\DateTimeInterface $expiryDate;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Email]
    #[ApiProperty('Email address associated with this account')]
    private ?string $email;

    #[ORM\OneToMany(targetEntity: AccountTransaction::class, mappedBy: 'account', orphanRemoval: true, cascade: ['remove'])]
    #[ApiProperty('Account transactions', true, false)]
    private Collection $transactions;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[ApiProperty(null,false, false)]
    private int $expiryNoticeLevel = 0;

    private ?float $coins;

    private ?float $frozenCoins;

    private ?Collection $campaigns = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->creationDate = new \DateTime();
        $this->expiryDate = new \DateTime($_ENV['APP_ACCOUNT_LIFETIME'] ?? static::LIFETIME);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(\DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|AccountTransaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(AccountTransaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setAccount($this);
            $this->calculateCoins();
        }

        return $this;
    }

    public function removeTransaction(AccountTransaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
            $this->calculateCoins();
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

    protected function calculateCoins(): void
    {
        $total = $frozen = 0;
        foreach ($this->getTransactions() as $transaction) {
            switch ($transaction->getOperation()) {
                case AccountTransaction::OP_AWARD:
                    $total += $transaction->getCoinsAmount();
                    break;
                case AccountTransaction::OP_DEDUCT:
                    $total -= $transaction->getCoinsAmount();
                    break;
                case AccountTransaction::OP_FREEZE:
                    $frozen += $transaction->getCoinsAmount();
                    break;
                default:
                    break;
            }
        }
        $this->coins = $total;
        $this->frozenCoins = $frozen;
    }

    #[ApiProperty('Total amount of coins on the account', true, false)]
    public function getCoins(): ?float
    {
        if (!isset($this->coins)) {
            $this->calculateCoins();
        }

        return $this->coins;
    }

    #[ApiProperty('Amount of coins frozen at the moment', true, false)]
    public function getFrozenCoins(): ?float
    {
        if (!isset($this->frozenCoins)) {
            $this->calculateCoins();
        }

        return $this->frozenCoins;
    }

    #[ApiProperty('Amount of coins available to be used', true, false)]
    public function getAvailableCoins(): ?float
    {
        if (!isset($this->frozenCoins, $this->coins)) {
            $this->calculateCoins();
        }

        return $this->coins - $this->frozenCoins;
    }

    #[ApiProperty('True if account is past its expiration date', true, false)]
    public function getExpired(): bool
    {
        $expiryDate = $this->getExpiryDate();

        return $expiryDate && $expiryDate < new \DateTime();
    }

    public function getExpiryNoticeLevel(): int
    {
        return $this->expiryNoticeLevel;
    }

    public function setExpiryNoticeLevel(int $expiryNoticeLevel): self
    {
        $this->expiryNoticeLevel = $expiryNoticeLevel;

        return $this;
    }

    /**
     * Returns campaigns used by this account
     * @return Collection|Campaign[]
     */
    #[ApiProperty(readable: true, writable: false, builtinTypes: [
        new Type(
            Type::BUILTIN_TYPE_OBJECT,
            false,
            Collection::class,
            true,
            new Type('int'),
            new Type(Type::BUILTIN_TYPE_OBJECT, false, Campaign::class)
        ),
    ])]
    public function getCampaigns(): Collection
    {
        if ($this->campaigns) {
            return $this->campaigns;
        }
        $campaigns = [];
        foreach ($this->getTransactions() as $transaction) {
            $campaign = $transaction->getCampaign();
            if ($campaign && !isset($campaigns[$campaign->getId()])) {
                $campaigns[$campaign->getId()] = $campaign;
            }
        }

        return $this->campaigns = new ArrayCollection(array_values($campaigns));
    }

}
