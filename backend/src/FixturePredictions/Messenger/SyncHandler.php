<?php

namespace App\FixturePredictions\Messenger;

use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\FixtureProvider;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SyncHandler
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
    public function __invoke(SyncMessage $message): void
    {
        $competition = $this->competitionRepository->findOneByCode($message->competition->value)
            ?? throw new Exception('Competition not found');
        $season = $this->seasonRepository->findOneByYear($message->year ?? 2025) // TODO: Remove hardcode.
            ?? throw new Exception('Season not found');
        $from = $message->from;
        $to = $message->to;

        if ($from === null || $to === null) {
            $timezone = new DateTimeZone('UTC');
            $from = (new DateTimeImmutable('-1 day', timezone: $timezone))->setTime(0, 0);
            $to = (new DateTimeImmutable('+1 day', timezone: $timezone))->setTime(0, 0);
        }

        $this->fixturesProvider->sync($competition, $season, $from, $to);
    }
}
