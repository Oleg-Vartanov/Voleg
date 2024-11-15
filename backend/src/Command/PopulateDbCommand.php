<?php

namespace App\Command;

use App\Entity\Country;
use App\Repository\CountryRepository;
use App\Service\CsvReader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:populate-db')]
class PopulateDbCommand extends Command
{
    public function __construct(
        private CsvReader $csvReader,
        private KernelInterface $kernel,
        private CountryRepository $countryRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->populateCountry($output);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function populateCountry(OutputInterface $output): void
    {
        if ($this->countryRepository->hasRecords()) {
            $output->writeln('Country table already populated');
            return;
        }

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
        foreach ($rows as $row) {
            $country = new Country();
            $country->setName($row[$headers['name']]);
            $country->setIso31661Alpha2($row[$headers['iso_3166_1_alpha_2']]);
            $country->setIso31661Alpha3($row[$headers['iso_3166_1_alpha_3']]);
            $country->setIso31661Numeric($row[$headers['iso_3166_1_numeric']]);
            $country->setIso31662(empty($row[$headers['iso_3166_2']]) ? null : $row[$headers['iso_3166_2']]);
            $this->entityManager->persist($country);
        }
        $this->entityManager->flush();

        $output->writeln('Country table was populated');
    }
}