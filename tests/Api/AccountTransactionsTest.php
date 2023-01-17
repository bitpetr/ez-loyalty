<?php

namespace App\Tests\Api;

use App\Entity\AccountTransaction;

class AccountTransactionsTest extends AbstractLoyaltyApiTestCase
{

    public function testGetItem(): void
    {
        $account = static::getTestAccount();
        $iri = $this->findIriBy(AccountTransaction::class, ['account' => $account['id']]);
        $this->getClient()->request('GET', $iri);
        static::assertResponseIsSuccessful();
        static::assertMatchesResourceItemJsonSchema(AccountTransaction::class);
    }

    public function testPostItem(): void
    {
        $item = self::getTestTransaction(true);
        $this->getClient()->request('POST', static::TRANSACTIONS_ENDPOINT, ['json' => $item]);
        static::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains($item);
        self::assertMatchesResourceItemJsonSchema(AccountTransaction::class, format: 'json');
    }

    public function testPatchItem(): void
    {
        $client = $this->getClient();

        $patch = [
            'currency' => 'EUR',
        ];
        $client->request(
            'PATCH',
            $this->findIriBy(AccountTransaction::class, ['account' => static::getTestAccount()['id']]),
            [
                'json' => $patch,
                'headers' => ['content-type' => 'application/merge-patch+json; charset=utf-8'],
            ]
        );
        static::assertResponseIsSuccessful();
        self::assertJsonContains($patch);
        self::assertMatchesResourceItemJsonSchema(AccountTransaction::class, format: 'json');
    }
}