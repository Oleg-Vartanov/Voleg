<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeasonFixture extends Fixture
{
    /** @var int Obviously used for test and possibly dev env. */
    public const int CURRENT_SEASON = 2024;

    public function load(ObjectManager $manager): void
    {
        $season = new Season();
        $season->setYear(self::CURRENT_SEASON);
        $manager->persist($season);

        $manager->flush();
        $this->addReference('season', $season);
    }
}
