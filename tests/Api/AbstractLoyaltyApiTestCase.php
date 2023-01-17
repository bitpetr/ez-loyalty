<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractLoyaltyApiTestCase extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public const ACCOUNTS_ENDPOINT = '/api/accounts';
    public const TRANSACTIONS_ENDPOINT = '/api/account_transactions';
    public const CAMPAIGNS_ENDPOINT = '/api/campaigns';

    private Client $client;

    protected static function getTestAccount(): array
    {
        return ['id' => 'TSTTAR', 'email' => 'test_target@local.dev'];
    }

    protected static function getTestCampaign(): array
    {
        return [
            'id' => 'TEST00',
            'name' => 'No restrictions',
            'rewardPercent' => 25,
            'singleUse' => false,
            'onlyNew' => false,
        ];
    }

    protected static function getTestTransaction(bool $withRelations = false): array
    {
        $transaction = [
            'operation' => 'award',
            'coinsAmount' => 11,
            'moneyAmount' => 11,
            'currency' => 'USD',
        ];

        if ($withRelations) {
            $transaction['account'] = static::ACCOUNTS_ENDPOINT.'/'.static::getTestAccount(
                )['id'];
            $transaction['campaign'] = static::CAMPAIGNS_ENDPOINT.'/'.static::getTestCampaign(
                )['id'];
        }

        return $transaction;
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(
            defaultOptions: [
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json; charset=utf-8',
                ],
            ]
        );
        $this->client->disableReboot();

        /** @see https://github.com/CedCannes/sf_bug_collections_empty_during_tests_with_fixtures */
        static::getContainer()->get('doctrine')->getManager()->clear();

        parent::setUp();
    }


    protected function getClient(): AbstractBrowser|Client|null
    {
        return $this->client;
    }
}