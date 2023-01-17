<?php

namespace App\Entity;

use App\Repository\DeletedAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeletedAccountRepository::class)]
class DeletedAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $code;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $creationDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $deletionDate;

    #[ORM\Column(type: 'float')]
    private ?float $coins;

    public function __construct(
        ?string $code = null,
        ?string $email = null,
        ?float $coins = null,
        \DateTimeInterface $creationDate = null,
        \DateTimeInterface $deletionDate = null,
    ) {
        $this->code = $code;
        $this->email = $email;
        $this->creationDate = $creationDate;
        $this->deletionDate = $deletionDate ?? new \DateTime();
        $this->coins = $coins;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDeletionDate(): ?\DateTimeInterface
    {
        return $this->deletionDate;
    }

    public function setDeletionDate(\DateTimeInterface $deletionDate): self
    {
        $this->deletionDate = $deletionDate;

        return $this;
    }

    public function getCoins(): ?float
    {
        return $this->coins;
    }

    public function setCoins(float $coins): self
    {
        $this->coins = $coins;

        return $this;
    }
}
