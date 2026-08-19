<?php

namespace App\FixturePredictions\Test\Trait;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

trait FootballFixtureTestTrait
{
    private int $footballTeamCounter = 0;

    /**
     * @return Fixture[]
     */
    protected function createFootballFixtures(
        int $count,
        DateTimeImmutable $startAt = new DateTimeImmutable('2025-01-01'),
    ): array {
        $em = $this->getService(EntityManagerInterface::class);
        [$competition, $season] = $this->requireCompetitionAndSeason();
        $fixtures = [];

        for ($i = 0; $i < $count; ++$i) {
            [$homeTeam, $awayTeam] = $this->createFootballTeams();
            $fixtures[] = $this->buildFootballFixture(
                $competition,
                $season,
                $homeTeam,
                $awayTeam,
                $startAt,
                100000 + $i,
            );
            $em->persist($fixtures[$i]);
        }

        $em->flush();

        return $fixtures;
    }

    protected function createFootballFixture(
        DateTimeImmutable $startAt = new DateTimeImmutable('2025-01-01'),
        int $providerFixtureId = 999999,
    ): Fixture {
        [$competition, $season] = $this->requireCompetitionAndSeason();
        [$homeTeam, $awayTeam] = $this->createFootballTeams();
        $fixture = $this->buildFootballFixture(
            $competition,
            $season,
            $homeTeam,
            $awayTeam,
            $startAt,
            $providerFixtureId,
        );

        $em = $this->getService(EntityManagerInterface::class);
        $em->persist($fixture);
        $em->flush();

        return $fixture;
    }

    protected function createFootballPrediction(
        User $user,
        Fixture $fixture,
        int $homeScore = 1,
        int $awayScore = 1,
        int $points = 0,
    ): FixturePrediction {
        $prediction = new FixturePrediction();
        $prediction->setUser($user);
        $prediction->setFixture($fixture);
        $prediction->setHomeScore($homeScore);
        $prediction->setAwayScore($awayScore);
        $prediction->setPoints($points);

        $em = $this->getService(EntityManagerInterface::class);
        $em->persist($prediction);
        $em->flush();

        return $prediction;
    }

    /**
     * @return array{0: Competition, 1: Season}
     */
    private function requireCompetitionAndSeason(): array
    {
        $competition = $this->getService(CompetitionRepository::class)
            ->findOneByCode(CompetitionCodeEnum::EPL->value);
        if ($competition === null) {
            throw new RuntimeException('Premier League competition must be seeded.');
        }

        $season = $this->getService(SeasonRepository::class)
            ->findOneByYear(SeasonSeeder::CURRENT_SEASON_YEAR);
        if ($season === null) {
            throw new RuntimeException(
                sprintf('Season %d must be seeded.', SeasonSeeder::CURRENT_SEASON_YEAR),
            );
        }

        return [$competition, $season];
    }

    private function buildFootballFixture(
        Competition $competition,
        Season $season,
        Team $homeTeam,
        Team $awayTeam,
        DateTimeImmutable $startAt,
        int $providerFixtureId,
    ): Fixture {
        $fixture = new Fixture();
        $fixture->setCompetition($competition);
        $fixture->setSeason($season);
        $fixture->setHomeTeam($homeTeam);
        $fixture->setAwayTeam($awayTeam);
        $fixture->setStatus(FixtureStatusEnum::Unknown);
        $fixture->setMatchday(1);
        $fixture->setStartAt($startAt);
        $fixture->setProviderFixtureId($providerFixtureId);

        return $fixture;
    }

    /**
     * @return array{0: Team, 1: Team}
     */
    private function createFootballTeams(): array
    {
        $em = $this->getService(EntityManagerInterface::class);
        $homeTeam = new Team();
        $homeTeam->setName('Test Team ' . ++$this->footballTeamCounter);
        $awayTeam = new Team();
        $awayTeam->setName('Test Team ' . ++$this->footballTeamCounter);
        $em->persist($homeTeam);
        $em->persist($awayTeam);

        return [$homeTeam, $awayTeam];
    }
}
