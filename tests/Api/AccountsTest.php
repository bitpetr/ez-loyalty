<?php

namespace App\Tests\Api;

use App\Entity\Account;

class AccountsTest extends AbstractLoyaltyApiTestCase
{
    public function testGetByEmail(): void
    {
        $client = $this->getClient();
        $client->request('GET', static::ACCOUNTS_ENDPOINT);
        self::assertJsonEquals([]);
        $account = static::getTestAccount();
        $client->request('GET', static::ACCOUNTS_ENDPOINT.'?'.http_build_query(['email' => $account['email']]));
        self::assertJsonContains([$account]);
        self::assertMatchesResourceCollectionJsonSchema(Account::class, format: 'json');
    }

    public function testPostAccount(): void
    {
        $account = [
            'email' => 'testpost@local.dev',
        ];
        $this->getClient()->request('POST', static::ACCOUNTS_ENDPOINT, [
            'json' => $account,
        ]);
        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains($account);
        self::assertMatchesResourceItemJsonSchema(Account::class, format: 'json');
    }
}