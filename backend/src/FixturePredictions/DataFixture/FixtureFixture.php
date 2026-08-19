<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Fixture as FpFixture;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Repository\TeamRepository;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use RuntimeException;

class FixtureFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly TeamRepository $teamRepository,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            TeamFixture::class,
        ];
    }

    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        if ($competition === null) {
            throw new RuntimeException('Premier League competition must be seeded before loading fixtures.');
        }

        $season = $this->seasonRepository->findOneByYear(SeasonSeeder::CURRENT_SEASON_YEAR);
        if ($season === null) {
            throw new RuntimeException(
                sprintf('Season %d must be seeded before loading fixtures.', SeasonSeeder::CURRENT_SEASON_YEAR),
            );
        }

        /** @var list<Team> $teams */
        $teams = $this->teamRepository->findBy([], ['id' => 'ASC']);
        if (count($teams) < TeamFixture::TEAM_COUNT) {
            throw new RuntimeException('Teams must be seeded before loading fixtures.');
        }

        foreach ($teams as $teamHome) {
            foreach ($teams as $teamAway) {
                if ($teamHome->getId() === $teamAway->getId()) {
                    continue;
                }

                $fixture = new FpFixture();
                $fixture->setSeason($season);
                $fixture->setCompetition($competition);
                $fixture->setHomeTeam($teamHome);
                $fixture->setAwayTeam($teamAway);
                $fixture->setHomeScore(random_int(0, 4));
                $fixture->setAwayScore(random_int(0, 4));
                $fixture->setStatus(FixtureStatusEnum::Unknown);
                $fixture->setMatchday(1);
                $fixture->setProviderFixtureId(1);
                $fixture->setStartAt(new DateTimeImmutable('2025-01-01'));

                $manager->persist($fixture);
            }
        }

        $manager->flush();
    }
}
