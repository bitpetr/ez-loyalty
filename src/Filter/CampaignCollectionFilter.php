<?php

namespace App\Filter;

use ApiPlatform\Api\FilterInterface;

class CampaignCollectionFilter implements FilterInterface
{

    public function getDescription(string $resourceClass): array
    {
        $description = [
            'id' => [
                'property' => 'id',
                'type' => 'string',
                'required' => true,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
            'account' => [
                'property' => 'account',
                'type' => 'string',
                'required' => false,
                'strategy' => 'exact',
                'is_collection' => false,
            ],
        ];

        return $description;
    }
}