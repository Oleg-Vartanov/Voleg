<?php

namespace App\Core\Service\Seeder;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Service\CsvReader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;

readonly class CurrencyCsvSeeder
{
    private const array HEADERS = [
        'Entity',
        'Currency',
        'AlphabeticCode',
        'NumericCode',
        'MinorUnit',
        'WithdrawalDate',
    ];

    public function __construct(
        private CsvReader $csvReader,
        private KernelInterface $kernel,
        private CurrencyRepository $currencyRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function seed(): void
    {
        $rows = $this->csvReader->read($this->kernel->getProjectDir() . '/data/currency.csv');
        $headers = array_shift($rows);

        if ($headers !== self::HEADERS) {
            throw new RuntimeException('Invalid currency CSV headers.');
        }

        $currenciesByCode = [];
        foreach ($this->currencyRepository->findAll() as $currency) {
            $currenciesByCode[$currency->getCode()] = $currency;
        }

        foreach ($rows as $row) {
            if (
                count($row) !== count(self::HEADERS)
                || !array_key_exists(1, $row)
                || !array_key_exists(2, $row)
                || !array_key_exists(4, $row)
                || !array_key_exists(5, $row)
            ) {
                continue;
            }

            $name = trim((string) $row[1]);
            $code = trim((string) $row[2]);
            $minorUnit = trim((string) $row[4]);
            $withdrawalDate = trim((string) $row[5]);

            if (
                $name === ''
                || $code === ''
                || $minorUnit === ''
                || !ctype_digit($minorUnit)
                || $withdrawalDate !== ''
            ) {
                continue;
            }

            $currency = $currenciesByCode[$code] ?? null;
            if ($currency === null) {
                $currency = new Currency(
                    name: $name,
                    code: $code,
                    decimalPlaces: (int) $minorUnit,
                );
                $this->entityManager->persist($currency);
                $currenciesByCode[$code] = $currency;
            }

            $currency->setName($name);
            $currency->setDecimalPlaces((int) $minorUnit);
        }

        $this->entityManager->flush();
    }
}
