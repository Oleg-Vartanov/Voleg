<?php

namespace App\Interface;

use App\Entity\Competition;
use App\Entity\Season;

interface FixturesProviderInterface
{
    public function syncTeams(Competition $competition, Season $season): void;

    public function syncFixtures(Competition $competition, Season $season): void;
}