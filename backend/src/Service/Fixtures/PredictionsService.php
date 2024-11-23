<?php

namespace App\Service\Fixtures;

use App\Entity\FixturePrediction;

class PredictionsService
{
    public function calculatePoints(FixturePrediction $prediction): int
    {
        $fixture = $prediction->getFixture();

        $homeScore = $fixture->getHomeScore();
        $awayScore = $fixture->getAwayScore();
        $pHomeScore = $prediction->getHomeScore();
        $pAwayScore = $prediction->getAwayScore();
        
        if ($homeScore === $pHomeScore && $awayScore === $pAwayScore) {
            return 3;
        }

        if (
            ($homeScore > $awayScore && $pHomeScore > $pAwayScore)
            || ($homeScore < $awayScore && $pHomeScore < $pAwayScore)
            || ($homeScore === $awayScore && $pHomeScore === $pAwayScore)
        ) {
            return 1;
        }

        return 0;
    }
}