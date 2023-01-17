<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\CampaignCollectionFilter;
use App\Repository\CampaignRepository;
use App\State\CampaignCollectionProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [new Get(), new GetCollection(provider: CampaignCollectionProvider::class)])]
#[ApiFilter(filterClass: CampaignCollectionFilter::class)]
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[UniqueEntity('id')]
class Campaign
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 16)]
    #[Assert\Regex(pattern: '/^[A-Z0-9]{4,16}$/', message: 'Valid symbols: A-Z, 0-9. 16 max.', htmlPattern: '^[A-Z0-9]{4,16}$')]
    #[ApiProperty('Campaign ID code')]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[ApiProperty('Campaign name')]
    private ?string $name;

    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(50)]
    #[ApiProperty('Suggested loyalty reward percent for the transaction with the campaign')]
    private ?int $rewardPercent;

    #[ORM\Column(type: 'boolean')]
    #[ApiProperty('Whether the campaign is single use or not')]
    private ?bool $singleUse = true;

    #[ORM\Column(type: 'boolean')]
    #[ApiProperty('Whether the campaign is only available for the new users')]
    private ?bool $onlyNew = true;

    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $validSince;

    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $validUntil;

    #[ApiProperty(readable: false, writable: false)]
    #[ORM\OneToMany(targetEntity: AccountTransaction::class, mappedBy: 'campaign')]
    private Collection $transactions;

    #[ApiProperty('Whether the campaign is available or not. '
        .'If an account id is provided, this also performs the account-related checks', true, false)]
    private ?bool $isAvailable = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->validSince = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): Campaign
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRewardPercent(): ?int
    {
        return $this->rewardPercent;
    }

    public function setRewardPercent(int $rewardPercent): self
    {
        $this->rewardPercent = $rewardPercent;

        return $this;
    }

    public function getSingleUse(): ?bool
    {
        return $this->singleUse;
    }

    public function setSingleUse(bool $singleUse): self
    {
        $this->singleUse = $singleUse;

        return $this;
    }

    public function getOnlyNew(): ?bool
    {
        return $this->onlyNew;
    }

    public function setOnlyNew(bool $onlyNew): self
    {
        $this->onlyNew = $onlyNew;

        return $this;
    }

    public function getValidSince(): \DateTimeInterface
    {
        return $this->validSince;
    }

    public function setValidSince(\DateTimeInterface $validSince): Campaign
    {
        $this->validSince = $validSince;

        return $this;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(\DateTimeInterface $validUntil = null): Campaign
    {
        $this->validUntil = $validUntil;

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
            $transaction->setCampaign($this);
        }

        return $this;
    }

    public function removeTransaction(AccountTransaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCampaign() === $this) {
                $transaction->setCampaign(null);
            }
        }

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(?bool $isAvailable): Campaign
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getId();
    }
}
