<?php

namespace App\FixturePredictions\Interface;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;

interface FixturesProviderInterface
{
    public function syncTeams(Competition $competition, Season $season): void;

    public function syncFixtures(Competition $competition, Season $season): void;
}