<?php

namespace App\Core\Command;

use App\Core\Service\Seeder\CountryCsvSeeder;
use App\Core\Service\Seeder\CurrencyCsvSeeder;
use App\FixturePredictions\Service\Seeder\CompetitionSeeder;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use App\SplitExpense\Service\Seeder\SeCategorySeeder;
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
        private readonly CurrencyCsvSeeder      $currencySeeder,
        private readonly SeasonSeeder           $seasonSeeder,
        private readonly CompetitionSeeder      $competitionSeeder,
        private readonly SeCategorySeeder        $categorySeeder,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->wrapInTransaction(function (): void {
            $this->countrySeeder->seed();
            $this->currencySeeder->seed();
            $this->seasonSeeder->seed();
            $this->competitionSeeder->seed();
            $this->categorySeeder->seed();
        });

        $io = new SymfonyStyle($input, $output);
        $io->success('Reference data synchronized.');

        return Command::SUCCESS;
    }
}
