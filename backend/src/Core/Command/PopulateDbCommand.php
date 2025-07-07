<?php

namespace App\Core\Command;

use App\Core\Entity\Country;
use App\Core\Repository\CountryRepository;
use App\Core\Service\CsvReader;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:populate-db')]
class PopulateDbCommand extends Command
{
    public function __construct(
        private readonly CsvReader $csvReader,
        private readonly KernelInterface $kernel,
        private readonly CountryRepository $countryRepository,
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->populateCountry($output);
        $this->populateCompetition($output);
        $this->populateSeason($output);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function populateCountry(OutputInterface $output): void
    {
        $folder = $this->kernel->getProjectDir().'/data/';
        $rows = $this->csvReader->read($folder.'country.csv');
        $headers = [
            'name' => 0,
            'iso_3166_1_alpha_2' => 1,
            'iso_3166_1_alpha_3' => 2,
            'iso_3166_1_numeric' => 3,
            'iso_3166_2' => 4,
        ];

        unset($rows[0]);

        $progressBar = new ProgressBar($output, count($rows));
        $progressBar->start();

        foreach ($rows as $row) {
            if ($this->countryRepository->findOneByName($row[$headers['name']]) !== null) {
                continue;
            }

            $country = new Country();
            $country->setName($row[$headers['name']]);
            $country->setIso31661Alpha2($row[$headers['iso_3166_1_alpha_2']]);
            $country->setIso31661Alpha3($row[$headers['iso_3166_1_alpha_3']]);
            $country->setIso31661Numeric($row[$headers['iso_3166_1_numeric']]);
            $country->setIso31662(empty($row[$headers['iso_3166_2']]) ? null : $row[$headers['iso_3166_2']]);

            $this->entityManager->persist($country);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $output->writeln(' Populated country table');
    }

    private function populateCompetition(OutputInterface $output): void
    {
        $rows = [['name' => 'Premier League', 'code' => 'PL', 'country_name' => 'England']];

        $progressBar = new ProgressBar($output, count($rows));
        $progressBar->start();

        foreach ($rows as $row) {
            if ($this->competitionRepository->findOneByCode($row['code']) !== null) {
                continue;
            }

            $competition = new Competition();
            $competition->setName($row['name']);
            $competition->setCode($row['code']);
            if ($country = $this->countryRepository->findOneByName($row['country_name'])) {
                $competition->setCountry($country);
            }

            $this->entityManager->persist($competition);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $output->writeln(' Populated competition table');
    }

    private function populateSeason(OutputInterface $output): void
    {
        $years = range(1992, 2100);

        $progressBar = new ProgressBar($output, count($years));
        $progressBar->start();

        foreach ($years as $year) {
            if ($this->seasonRepository->findOneByYear($year) !== null) {
                continue;
            }

            $season = new Season();
            $season->setYear($year);

            $this->entityManager->persist($season);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $output->writeln(' Populated season table');
    }
}