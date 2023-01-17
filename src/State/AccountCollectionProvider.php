<?php


namespace App\State;


use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;

class AccountCollectionProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?array
    {
        //only allow filtered requests
        if($operation instanceof CollectionOperationInterface && ($email = $context['filters']['email'] ?? null)) {
            return $this->entityManager->getRepository(Account::class)->findBy(['email'=>$email]);
        }

        return [];
    }
}