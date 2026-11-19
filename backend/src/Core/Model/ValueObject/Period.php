<?php

namespace App\Core\Model\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

readonly class Period
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
    ) {
        if ($end < $start) {
            throw new InvalidArgumentException('Period end must not be before its start.');
        }
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }
}
