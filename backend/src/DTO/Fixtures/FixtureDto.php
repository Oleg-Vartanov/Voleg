<?php

namespace App\DTO\Fixtures;

use App\Entity\Team;
use App\Enum\Fixtures\FixtureStatusEnum;
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