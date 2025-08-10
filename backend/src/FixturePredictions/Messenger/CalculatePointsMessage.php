<?php

namespace App\FixturePredictions\Messenger;

readonly class CalculatePointsMessage
{
    public function __construct(
        private int $fixtureId
    ) {
    }

    public function getFixtureId(): int
    {
        return $this->fixtureId;
    }
}
