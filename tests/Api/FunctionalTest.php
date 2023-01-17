<?php

namespace App\Tests\Api;

use App\Entity\Account;
use App\Entity\AccountTransaction;
use Doctrine\ORM\EntityManagerInterface;

class FunctionalTest extends AbstractLoyaltyApiTestCase
{
    public function testCoinDeduction(): void
    {
        $client = $this->getClient();

        $post = [
            'account' => static::ACCOUNTS_ENDPOINT.'/'.static::getTestAccount()['id'],
            'operation' => 'freeze',
            'coinsAmount' => 1,
            'moneyAmount' => 1,
            'currency' => 'USD',
        ];

        $transaction = $this->postTransactionToApi($post);
        $account = $this->fetchAccountFromApi();
        static::assertEquals(1, $account['frozenCoins']);
        static::assertEquals(10, $account['availableCoins']);

        $client->request('PATCH', static::TRANSACTIONS_ENDPOINT.'/'.$transaction['id'], [
            'json' => [
                'operation' => 'deduct',
            ],
            'headers' => ['content-type' => 'application/merge-patch+json; charset=utf-8'],
        ]);
        static::assertResponseIsSuccessful();
        $account = $this->fetchAccountFromApi();
        static::assertEquals(0, $account['frozenCoins']);
        static::assertEquals(10, $account['availableCoins']);
    }

    /**
     * @dataProvider campaignAccountsProvider
     */
    public function testCampaignAvailability(string $campaignId, ?string $accountId, bool $expectedAvailability): void
    {
        $campaign = $this->fetchCampaignFromApi($campaignId, $accountId);
        self::assertEquals($campaign['isAvailable'], $expectedAvailability);
    }

    public function campaignAccountsProvider(): \Generator
    {
        $c00 = 'TEST00';
        $c01 = 'TEST01';
        $c10 = 'TEST10';
        $c11 = 'TEST11';
        $cNotStarted = 'NOTSTARTED';
        $cFinished = 'FINISHED';

        yield [$c00, null, true];
        yield [$c01, null, true];
        yield [$c10, null, true];
        yield [$c11, null, true];
        yield [$cNotStarted, null, false];
        yield [$cFinished, null, false];

        yield [$c00, 'AFRESH', true];
        yield [$c01, 'AFRESH', false];
        yield [$c10, 'AFRESH', true];
        yield [$c11, 'AFRESH', false];
        yield [$cNotStarted, 'AFRESH', false];
        yield [$cFinished, 'AFRESH', false];

        yield [$c00, 'HASCAM', true];
        yield [$c01, 'HASCAM', false];
        yield [$c10, 'HASCAM', false];
        yield [$c11, 'HASCAM', false];
    }

    protected function postTransactionToApi(array $transaction): array
    {
        return json_decode(
            $this->getClient()->request('POST', static::TRANSACTIONS_ENDPOINT, ['json' => $transaction])->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    protected function fetchAccountFromApi(string $id = null): ?array
    {
        return json_decode(
            $this->getClient()->request(
                'GET',
                static::ACCOUNTS_ENDPOINT.'/'.($id ?? static::getTestAccount()['id'])
            )->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    protected function fetchCampaignFromApi(string $id = null, string $accountId = null): ?array
    {
        $uri = static::CAMPAIGNS_ENDPOINT.'/'.($id ?? static::getTestCampaign()['id']);
        if ($accountId) {
            $uri .= '?account='.$accountId;
        }

        return json_decode(
            $this->getClient()->request(
                'GET',
                $uri
            )->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    protected static function fetchAccountFromDb(string $id = null): ?Account
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Account::class)
            ->find($id ?? static::getTestAccount()['id']);
    }

    protected static function fetchTransactionFromDb(string $id): ?AccountTransaction
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(AccountTransaction::class)
            ->find($id);
    }

    protected static function fetchAccountTransactionsFromDb(string $accountId = null): null|array|AccountTransaction
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(AccountTransaction::class)
            ->findBy(['account' => $accountId ?? static::getTestAccount()['id']]);
    }
}