<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixture extends Fixture
{
    public const int TEAM_COUNT = 20;

    public function load(ObjectManager $manager): void
    {
        foreach (range(1, self::TEAM_COUNT) as $index) {
            $team = new Team();
            $team->setName('Team ' . $index);
            $manager->persist($team);
        }

        $manager->flush();
    }
}
