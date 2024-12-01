<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:test-email')]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('no-reply@voleg.me')
            ->to('olegvartanov1997@gmail.com')
            ->subject('Test Email Subject')
            ->text('Test Text')
        ;

        $this->mailer->send($email);

        return Command::SUCCESS;
    }
}