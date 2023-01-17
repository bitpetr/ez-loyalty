<?php

namespace App\Command;

use App\Repository\AccountTransactionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:transaction:release-frozen',
    description: 'Resets account transactions what are frozen for a while, releasing the funds',
)]
class TransactionReleaseFrozenCommand extends Command
{
    private AccountTransactionRepository $transactionRepository;

    public function __construct(AccountTransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument(
                'interval',
                InputArgument::OPTIONAL,
                'Time interval since last transaction update, after which it should be released',
                '1 hour'
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $interval = $input->getArgument('interval');

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
            $count = $this->transactionRepository->countFrozen($interval);
        } else {
            $count = $this->transactionRepository->releaseFrozen($interval);
        }

        $io->success(sprintf('[%s] Released "%d" frozen account transactions.', date('d.m.Y H:i:s'), $count));

        return Command::SUCCESS;
    }
}
