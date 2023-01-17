<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AccountTransactionRepository;
use App\State\AccountTransactionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Get(),
    new Put(),
    new Patch(),
    new Post(processor: AccountTransactionProcessor::class),
])]
#[ORM\Entity(repositoryClass: AccountTransactionRepository::class)]
class AccountTransaction
{
    public const OP_AWARD = 'award';        //increase coin balance
    public const OP_DEDUCT = 'deduct';      //decrease coin balance
    public const OP_FREEZE = 'freeze';      //increase frozen balance
    public const OP_RELEASE = 'release';    //do nothing, cancelled freeze

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty('Transaction ID',true,false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty('Loyalty account associated with this transaction')]
    private ?Account $account;

    #[ORM\Column(type: 'string', length: 32)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getOperations')]
    #[ApiProperty('One of the four supported operations: '
        .self::OP_AWARD.', '
        .self::OP_DEDUCT.', '
        .self::OP_FREEZE.', '
        .self::OP_RELEASE)]
    private ?string $operation;

    #[ORM\Column(type: 'datetime')]
    #[ApiProperty('Transaction date and time',true,false)]
    private ?\DateTimeInterface $time;

    #[ORM\Column(type: 'float')]
    #[ApiProperty('Transaction value in loyalty coins')]
    private ?float $coinsAmount;

    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty('Transaction value in actual money')]
    private ?float $moneyAmount;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    #[Assert\Currency]
    #[ApiProperty('Currency code for monetary value')]
    private ?string $currency;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[ApiProperty('External order ID associated with the transaction')]
    private ?int $orderId;

    #[ORM\ManyToOne(targetEntity: ApiClient::class, inversedBy: 'accountTransactions')]
    #[ApiProperty('Set automatically as a POSTed API client', true, false)]
    private ?ApiClient $source;

    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'transactions')]
    #[ApiProperty('Promotion campaign associated with this transaction')]
    private ?Campaign $campaign;

    public function __construct()
    {
        $this->time = new \DateTime();
    }

    public static function getOperations(): array
    {
        return [self::OP_AWARD, self::OP_DEDUCT, self::OP_FREEZE, self::OP_RELEASE];
    }

    public static function getCurrencyCodes(): array
    {
        return ['USD', 'EUR'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getCoinsAmount(): ?float
    {
        return $this->coinsAmount;
    }

    public function setCoinsAmount(float $coinsAmount): self
    {
        $this->coinsAmount = $coinsAmount;

        return $this;
    }

    public function getMoneyAmount(): ?float
    {
        return $this->moneyAmount;
    }

    public function setMoneyAmount(float $moneyAmount): self
    {
        $this->moneyAmount = $moneyAmount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(?int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

    public function getSource(): ?ApiClient
    {
        return $this->source;
    }

    public function setSource(?ApiClient $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getValue(): string
    {
        return sprintf('%.2f (%.2f %s)', $this->getCoinsAmount(), $this->getMoneyAmount(), $this->getCurrency());
    }
}