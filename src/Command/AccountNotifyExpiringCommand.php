<?php

namespace App\Command;

use App\Repository\AccountRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:account:notify-expiring',
    description: 'Sends email notices about account expiration to their owners',
)]
class AccountNotifyExpiringCommand extends Command
{
    private AccountRepository $repository;
    private MailerInterface $mailer;

    public function __construct(AccountRepository $repository, MailerInterface $mailer)
    {
        $this->repository = $repository;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->addArgument('interval', InputArgument::OPTIONAL, 'Time interval until the expiration', '1 week')
            ->addArgument('level', InputArgument::OPTIONAL, 'Account notice level to filter', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $accounts = $this->repository->getExpiringForNotice(
            $input->getArgument('interval'),
            $input->getArgument('level')
        );
        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
        } else {
            $email = (new TemplatedEmail())
                ->from($_ENV['APP_ADMIN_EMAIL'] ?? 'root@localhost')
                ->subject('Your loyalty account is expiring soon!')
                ->htmlTemplate('email/expiry_notice.html.twig');
            foreach ($accounts as $account) {
                $email->to($account->getEmail());
                $email->context(['account'=> $account]);
                try {
                    $this->mailer->send($email);
                    $account->setExpiryNoticeLevel($account->getExpiryNoticeLevel() + 1);
                } catch (TransportExceptionInterface $e) {
                    $io->warning(sprintf('Error sending email to %s: %s', $account->getEmail(), $e->getMessage()));
                }
            }
            $this->repository->getEntityManager()->flush();
        }

        $io->success(sprintf('[%s] Notified "%d" expiring accounts.', date('d.m.Y H:i:s'), count($accounts)));

        return Command::SUCCESS;
    }
}
