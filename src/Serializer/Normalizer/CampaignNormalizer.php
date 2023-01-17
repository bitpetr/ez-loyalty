<?php

namespace App\Serializer\Normalizer;

use App\Entity\Campaign;
use App\Service\CampaignAvailabilityChecker;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class CampaignNormalizer implements NormalizerAwareInterface, ContextAwareNormalizerInterface
{
    use NormalizerAwareTrait;
    private const ALREADY_CALLED = 'CAMPAIGN_NORMALIZER_ALREADY_CALLED';
    private CampaignAvailabilityChecker $checker;

    public function __construct(CampaignAvailabilityChecker $checker)
    {
        $this->checker = $checker;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return empty($context[self::ALREADY_CALLED]) && $data instanceof Campaign;
    }

    public function normalize($object, $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->setIsAvailable($this->checker->isCampaignAvailable($object, $context['account'] ?? null));

        return $this->normalizer->normalize($object, $format, $context);
    }
}