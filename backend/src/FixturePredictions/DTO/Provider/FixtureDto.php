<?php

namespace App\FixturePredictions\DTO\Provider;

use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use DateTimeImmutable;

class FixtureDto
{
    public int $providerFixtureId;
    public FixtureStatusEnum $status;
    public int $matchday;
    public Team $homeTeam;
    public Team $awayTeam;
    public ?int $homeScore;
    public ?int $awayScore;
    public DateTimeImmutable $startAt;
}
