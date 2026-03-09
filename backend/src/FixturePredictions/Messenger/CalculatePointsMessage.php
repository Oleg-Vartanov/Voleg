<?php

namespace App\FixturePredictions\Messenger;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('fixture_predictions')]
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
