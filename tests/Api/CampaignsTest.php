<?php

namespace App\Tests\Api;

use App\Entity\Campaign;

class CampaignsTest extends AbstractLoyaltyApiTestCase
{
    public function testGetItem(): void
    {
        $client = $this->getClient();
        $client->request('GET', static::CAMPAIGNS_ENDPOINT);
        self::assertResponseStatusCodeSame(400);
        $campaign = static::getTestCampaign();
        $client->request('GET', static::CAMPAIGNS_ENDPOINT.'/'.$campaign['id']);
        static::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(Campaign::class, format: 'json');
        self::assertJsonContains($campaign);
    }
}