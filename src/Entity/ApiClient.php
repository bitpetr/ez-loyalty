<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Repository\ApiClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(operations: [new Get(normalizationContext: ['groups' => ['read']])])]
#[ORM\Entity(repositoryClass: ApiClientRepository::class)]
class ApiClient implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('read')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('read')]
    private ?string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $token;

    #[ORM\Column(type: 'array')]
    private array $allowedIpAdresses = [];

    #[ORM\OneToMany(targetEntity: AccountTransaction::class, mappedBy: 'source')]
    private Collection $accountTransactions;

    public function __construct()
    {
        $this->accountTransactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAllowedIpAdresses(): ?array
    {
        return $this->allowedIpAdresses;
    }

    public function setAllowedIpAdresses(array $allowedIpAdresses): self
    {
        $this->allowedIpAdresses = $allowedIpAdresses;

        return $this;
    }

    public function isIpAddressAllowed(string $ip): bool
    {
        if(!filter_var($ip, FILTER_VALIDATE_IP)){
            return false;
        }
        foreach ($this->getAllowedIpAdresses() as $allowedIpAdress) {
            if (static::ipBelongs($ip, $allowedIpAdress)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether an ip matches the network
     *
     * This function compares addresses by parts (separated by either '.' or ':'),
     * with '0' in network acting like a wildcard, matching everything.
     *
     * @param string $ip
     * @param string $network
     * @return bool
     */
    public static function ipBelongs(string $ip, string $network): bool
    {
        $ipParts = explode('.',str_replace(':','.',$ip));
        $networkParts = explode('.',str_replace(':','.',$network));

        if(count($ipParts) !== count($networkParts)) {
            return false;
        }

        foreach ($networkParts as $i => $networkPart) {
            $ipPart = $ipParts[$i] ?? false;
            if ($ipPart === false) {
                return false;
            }

            if((int)$networkPart !== 0 && $ipPart !== $networkPart) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Collection|AccountTransaction[]
     */
    public function getAccountTransactions(): Collection
    {
        return $this->accountTransactions;
    }

    public function addAccountTransaction(AccountTransaction $accountTransaction): self
    {
        if (!$this->accountTransactions->contains($accountTransaction)) {
            $this->accountTransactions[] = $accountTransaction;
            $accountTransaction->setSource($this);
        }

        return $this;
    }

    public function removeAccountTransaction(AccountTransaction $accountTransaction): self
    {
        if ($this->accountTransactions->removeElement($accountTransaction)) {
            // set the owning side to null (unless already changed)
            if ($accountTransaction->getSource() === $this) {
                $accountTransaction->setSource(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_API_CLIENT'];
    }

    public function getPassword(): void
    {
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): ?string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): ?string
    {
        return $this->getToken();
    }
}
