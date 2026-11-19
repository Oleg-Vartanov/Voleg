<?php

namespace App\FixturePredictions\Model\DTO\Provider;

use App\FixturePredictions\Model\Entity\Competition;
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
