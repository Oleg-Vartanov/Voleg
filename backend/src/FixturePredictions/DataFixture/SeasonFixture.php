<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeasonFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (range(1992, 2100) as $year) {
            $s = new Season();
            $s->setYear($year);
            $manager->persist($s);
        }

        $this->addReference('season', $s);

        $manager->flush();
    }
}
