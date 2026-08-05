<?php

namespace App\Core\Service\Seeder;

use App\Core\Entity\Country;
use App\Core\Repository\CountryRepository;
use App\Core\Service\CsvReader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;

readonly class CountryCsvSeeder
{
    private const array HEADERS = [
        'name',
        'iso_3166_1_alpha_2',
        'iso_3166_1_alpha_3',
        'iso_3166_1_numeric',
        'iso_3166_2',
    ];

    public function __construct(
        private CsvReader $csvReader,
        private KernelInterface $kernel,
        private CountryRepository $countryRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function seed(): int
    {
        $rows = $this->csvReader->read($this->kernel->getProjectDir() . '/data/country.csv');
        $headers = array_shift($rows);

        if ($headers !== self::HEADERS) {
            throw new RuntimeException('Invalid country CSV headers.');
        }

        $countriesByName = [];
        foreach ($this->countryRepository->findAll() as $country) {
            $countriesByName[$country->getName()] = $country;
        }

        foreach ($rows as $rowNumber => $row) {
            if (
                count($row) !== count(self::HEADERS)
                || !isset($row[0], $row[1], $row[2], $row[3])
                || !array_key_exists(4, $row)
            ) {
                throw new RuntimeException(sprintf('Invalid country CSV data on row %d.', $rowNumber + 2));
            }

            $name = $row[0];
            $alpha2 = $row[1];
            $alpha3 = $row[2];
            $numeric = $row[3];
            $subdivision = $row[4];
            $country = $countriesByName[$name] ?? new Country();

            $country->setName($name);
            $country->setIso31661Alpha2($alpha2);
            $country->setIso31661Alpha3($alpha3);
            $country->setIso31661Numeric((int) $numeric);
            $country->setIso31662(empty($subdivision) ? null : $subdivision);

            if ($country->getId() === null) {
                $this->entityManager->persist($country);
                $countriesByName[$name] = $country;
            }
        }

        $this->entityManager->flush();

        return count($rows);
    }
}
