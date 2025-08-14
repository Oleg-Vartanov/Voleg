<?php

namespace App\FixturePredictions\Scheduler;

use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\FixtureProvider;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

// At every 10th minute.
#[AsCronTask('*/10 * * * *', schedule: 'fixture_predictions', transports: 'default')]
readonly class Sync
{
    public function __construct(
        private CompetitionRepository $competitionRepository,
        private SeasonRepository $seasonRepository,
        private FixtureProvider $fixturesProvider,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025); // TODO: Remove hardcode.

        $timezone = new DateTimeZone('UTC');
        $from = (new DateTimeImmutable('-1 day', timezone: $timezone))->setTime(0, 0);
        $to = (new DateTimeImmutable('+1 day', timezone: $timezone))->setTime(0, 0);

        $this->fixturesProvider->sync($competition, $season, $from, $to);
    }
}
