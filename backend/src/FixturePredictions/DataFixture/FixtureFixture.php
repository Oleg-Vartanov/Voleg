<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture as FpFixture;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FixtureFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $teams = [];
        foreach (range(1,20) as $index) {
            $teams[] = $this->getReference('team_' . $index, Team::class);
        }
        $competition = $this->getReference('competition_PL', Competition::class);
        $season = $this->getReference('season', Season::class);

        $index = 1;
        foreach ($teams as $teamHome) {
            foreach ($teams as $teamAway) {
                if ($teamHome->getId() === $teamAway->getId()) {
                    continue;
                }

                $f = new FpFixture();
                $f->setSeason($season);
                $f->setCompetition($competition);
                $f->setHomeTeam($teamHome);
                $f->setAwayTeam($teamAway);
                $f->setHomeScore(random_int(0,4));
                $f->setAwayScore(random_int(0,4));
                $f->setStatus(FixtureStatusEnum::Unknown);
                $f->setMatchday(1);
                $f->setProviderFixtureId(1);
                $f->setStartAt(new DateTimeImmutable('2025-01-01'));

                $manager->persist($f);

                $this->addReference('fixture_'.$index++, $f);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeamFixture::class,
            SeasonFixture::class,
            CompetitionFixture::class,
        ];
    }
}
