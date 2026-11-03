<?php

namespace App\Core\Service\Seeder;

use App\FixturePredictions\Service\Seeder\CompetitionSeeder;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use App\SplitExpense\Service\Seeder\CategorySeeder;
use Exception;

readonly class SeederService
{
    public function __construct(
        private CountryCsvSeeder $countrySeeder,
        private CurrencyCsvSeeder $currencySeeder,
        private SeasonSeeder $seasonSeeder,
        private CompetitionSeeder $competitionSeeder,
        private CategorySeeder $categorySeeder,
    ) {
    }

    /**
     * @throws Exception
     */
    public function seed(): void
    {
        $this->countrySeeder->seed();
        $this->currencySeeder->seed();
        $this->seasonSeeder->seed();
        $this->competitionSeeder->seed();
        $this->categorySeeder->seed();
    }
}
