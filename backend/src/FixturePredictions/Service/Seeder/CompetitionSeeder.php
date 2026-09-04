<?php

namespace App\FixturePredictions\Service\Seeder;

use App\Core\Repository\CountryRepository;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use RuntimeException;

readonly class CompetitionSeeder
{
    public function __construct(
        private CountryRepository $countryRepository,
        private CompetitionRepository $competitionRepository,
        private SeasonRepository $seasonRepository,
    ) {
    }

    public function seed(): void
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

        if ($competition->getCurrentSeason() === null) {
            $season = $this->seasonRepository->findOneByYear(SeasonSeeder::CURRENT_SEASON_YEAR);
            if ($season === null) {
                throw new RuntimeException('Season must be seeded before setting to the competition.');
            }

            $competition->setCurrentSeason($season);
            $this->competitionRepository->save($competition, true);
        }
    }
}
