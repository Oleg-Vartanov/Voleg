<?php

namespace App\Core\Command;

use App\Core\Service\Seeder\CountryCsvSeeder;
use App\FixturePredictions\Service\Seeder\CompetitionSeeder;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-reference-data',
    description: 'Synchronize production reference data',
    aliases: ['app:populate-db'],
)]
class SeedReferenceDataCommand extends Command
{
    public function __construct(
        private readonly CountryCsvSeeder       $countrySeeder,
        private readonly SeasonSeeder           $seasonSeeder,
        private readonly CompetitionSeeder      $competitionSeeder,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        [$countryCount, $seasonCount, $competitionCount] = $this->entityManager->wrapInTransaction(
            fn (): array => [
                $this->countrySeeder->seed(),
                $this->seasonSeeder->seed(),
                $this->competitionSeeder->seed(),
            ],
        );

        $io = new SymfonyStyle($input, $output);
        $io->success(sprintf(
            'Reference data synchronized: %d countries, %d seasons, %d competitions.',
            $countryCount,
            $seasonCount,
            $competitionCount,
        ));

        return Command::SUCCESS;
    }
}
