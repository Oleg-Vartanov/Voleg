<?php

namespace App\FixturePredictions\DTO\Provider;

use App\FixturePredictions\Entity\Competition;
use DateTimeImmutable;

class SeasonDto
{
    public function __construct(
        public Competition $competition,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
    ) {
    }
}
