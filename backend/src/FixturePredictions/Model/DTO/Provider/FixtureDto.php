<?php

namespace App\FixturePredictions\Model\DTO\Provider;

use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Model\Entity\Team;
use DateTimeImmutable;

class FixtureDto
{
    public function __construct(
        public int $providerFixtureId,
        public FixtureStatusEnum $status,
        public int $matchday,
        public Team $homeTeam,
        public Team $awayTeam,
        public ?int $homeScore,
        public ?int $awayScore,
        public DateTimeImmutable $startAt,
    ) {
    }
}
