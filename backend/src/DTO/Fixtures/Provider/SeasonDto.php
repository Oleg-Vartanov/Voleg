<?php

namespace App\DTO\Fixtures\Provider;

use App\Entity\Competition;
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