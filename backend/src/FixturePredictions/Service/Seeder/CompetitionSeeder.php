<?php

namespace App\FixturePredictions\Service\Seeder;

use App\Core\Repository\CountryRepository;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use RuntimeException;

readonly class CompetitionSeeder
{
    public function __construct(
        private CountryRepository $countryRepository,
        private CompetitionRepository $competitionRepository,
    ) {
    }

    public function seed(): int
    {
        $country = $this->countryRepository->findOneByName('England');
        if ($country === null) {
            throw new RuntimeException('England must be seeded before its competitions.');
        }

        $code = CompetitionCodeEnum::EPL->value;
        $competition = $this->competitionRepository->findOneByCode($code);
        if ($competition === null) {
            $competition = new Competition();
            $competition->setName('Premier League');
            $competition->setCode($code);
            $competition->setCountry($country);

            $this->competitionRepository->save($competition, true);
        }

        return 1;
    }
}
