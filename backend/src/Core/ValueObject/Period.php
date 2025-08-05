<?php

namespace App\Core\ValueObject;

use DateTimeInterface;

readonly class Period
{
    public function __construct(
        private DateTimeInterface $start,
        private DateTimeInterface $end,
    ) {
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }
}
