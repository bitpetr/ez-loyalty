<?php

namespace App\ContextBuilder;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\Account;
use App\Entity\Campaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CampaignContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decorated;
    private EntityManagerInterface $entityManager;

    public function __construct(SerializerContextBuilderInterface $decorated, EntityManagerInterface $entityManager)
    {
        $this->decorated = $decorated;
        $this->entityManager = $entityManager;
    }

    /**
     * Add optional account context to Campaign resource requests
     *
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @return array
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (($context['resource_class'] ?? null) === Campaign::class && (($accountId = $request->get('account')))) {
            $repo = $this->entityManager->getRepository(Account::class);
            if (str_contains($accountId, '@')) {
                $account = $repo->findOneBy(['email' => $accountId]);
            } else {
                $account = $repo->find($accountId);
            }
            $context['account'] = $account;
        }

        return $context;
    }
}