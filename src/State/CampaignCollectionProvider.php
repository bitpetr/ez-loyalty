<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Campaign;
use Doctrine\ORM\EntityManagerInterface;

class CampaignCollectionProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Provide campaign filtered by ID and account
     *
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return array|object[]|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?array
    {
        if ($operation instanceof CollectionOperationInterface) {
            $id = $context['filters']['id'];
            $campaigns = $this->entityManager->getRepository(Campaign::class)->findBy(['id' => $id]);
            return $campaigns;
        }
        return null;
    }
}