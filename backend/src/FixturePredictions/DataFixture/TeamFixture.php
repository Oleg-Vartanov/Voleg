<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixture extends Fixture
{
    private const array TEAM_NAMES = [
        'Arsenal',
        'Aston Villa',
        'Bournemouth',
        'Brentford',
        'Brighton Hove',
        'Chelsea',
        'Crystal Palace',
        'Everton',
        'Fulham',
        'Ipswich Town',
        'Leicester City',
        'Liverpool',
        'Man City',
        'Man United',
        'Newcastle',
        'Nottingham',
        'Southampton',
        'Tottenham',
        'West Ham',
        'Wolverhampton',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TEAM_NAMES as $name) {
            $team = new Team();
            $team->setName($name);
            $manager->persist($team);
        }

        $manager->flush();
    }
}
