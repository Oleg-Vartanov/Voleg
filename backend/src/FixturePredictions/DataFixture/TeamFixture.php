<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (range(1, 20) as $index) {
            $team = new Team();
            $team->setName('Team ' . $index);
            $manager->persist($team);

            $this->addReference('team_' . $index, $team);
        }

        $manager->flush();
    }
}
