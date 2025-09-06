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
        foreach (range(1992, 2100) as $year) {
            $s = new Season();
            $s->setYear($year);
            $manager->persist($s);

            if ($year === self::CURRENT_SEASON) {
                $this->addReference('season', $s);
            }
        }


        $manager->flush();
    }
}
