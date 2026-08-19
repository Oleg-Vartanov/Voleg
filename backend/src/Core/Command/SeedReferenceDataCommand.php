<?php

namespace App\Core\Command;

use App\Core\Service\Seeder\SeederService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-reference-data',
    description: 'Synchronize production reference data',
)]
class SeedReferenceDataCommand extends Command
{
    public function __construct(
        private readonly SeederService $seederService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->wrapInTransaction(function (): void {
            $this->seederService->seed();
        });

        $io = new SymfonyStyle($input, $output);
        $io->success('Reference data synchronized.');

        return Command::SUCCESS;
    }
}
