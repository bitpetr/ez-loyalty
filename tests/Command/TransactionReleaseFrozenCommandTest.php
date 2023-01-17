<?php

namespace App\Tests\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Console\Tester\CommandTester;

class TransactionReleaseFrozenCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use MailerAssertionsTrait;

    public function testCommand(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:transaction:release-frozen');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['interval' => '1 hour']);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Released "1" frozen account transactions.', $output);
    }

    protected function setUp(): void
    {
        /** @see https://github.com/CedCannes/sf_bug_collections_empty_during_tests_with_fixtures */
        static::getContainer()->get('doctrine')->getManager()->clear();
    }
}
