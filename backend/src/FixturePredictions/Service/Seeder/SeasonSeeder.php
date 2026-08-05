<?php

namespace App\FixturePredictions\Service\Seeder;

use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SeasonSeeder
{
    public const int FIRST_YEAR = 1992;
    public const int LAST_YEAR = 2100;

    public function __construct(
        private SeasonRepository $seasonRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function seed(): void
    {
        $existingYears = [];
        foreach ($this->seasonRepository->findAll() as $season) {
            $existingYears[$season->getYear()] = true;
        }

        $range = range(self::FIRST_YEAR, self::LAST_YEAR);
        foreach ($range as $year) {
            if (isset($existingYears[$year])) {
                continue;
            }

            $season = new Season();
            $season->setYear($year);
            $this->entityManager->persist($season);
        }

        $this->entityManager->flush();
    }
}
