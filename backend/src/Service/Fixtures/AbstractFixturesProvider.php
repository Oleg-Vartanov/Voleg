<?php

namespace App\Service\Fixtures;

use App\DTO\Fixtures\Provider\FixtureDto;
use App\DTO\Fixtures\Provider\TeamDto;
use App\Entity\Competition;
use App\Entity\Fixture;
use App\Entity\Season;
use App\Entity\Team;
use App\Interface\FixturesProviderInterface;
use App\Repository\FixtureRepository;
use App\Repository\TeamRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

abstract readonly class AbstractFixturesProvider implements FixturesProviderInterface
{
    public function __construct(
        protected TeamRepository $teamRepository,
        protected FixtureRepository $fixtureRepository,
        protected EntityManagerInterface $entityManager,
        protected PredictionsService $predictionsService,
    ) {
    }

    /**
     * @return TeamDto[]
     */
    abstract protected function getTeams(Competition $competition, Season $season): array;

    /**
     * @return FixtureDto[]
     */
    abstract protected function getFixtures(
        Competition $competition,
        Season $season,
        ?DateTime $from = null,
        ?DateTime $to = null,
    ): array;

    /**
     * @throws Exception
     */
    public function syncTeams(Competition $competition, Season $season): void
    {
        $teamsDtos = $this->getTeams($competition, $season);

        foreach ($teamsDtos as $teamDto) {
            $team = $this->teamRepository->findOneByProviderTeamId($teamDto->providerTeamId);

            if ($team !== null) {
                continue;
            }

            $team = new Team();
            $team->setName($teamDto->name);
            $team->setProviderTeamId($teamDto->providerTeamId);

            $this->entityManager->persist($team);
        }

        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function syncFixtures(
        Competition $competition,
        Season $season,
        ?DateTime $from = null,
        ?DateTime $to = null,
    ): void {
        $fixturesDtos = $this->getFixtures($competition, $season, $from, $to);

        foreach ($fixturesDtos as $fixtureDto) {
            $fixture = $this->fixtureRepository->findOneByProviderFixtureId($fixtureDto->providerFixtureId);

            if ($fixture === null) {
                $fixture = new Fixture();
                $fixture->setProviderFixtureId($fixtureDto->providerFixtureId);
            }
            $fixture->setCompetition($competition);
            $fixture->setSeason($season);
            $fixture->setStatus($fixtureDto->status);
            $fixture->setMatchday($fixtureDto->matchday);
            $fixture->setHomeTeam($fixtureDto->homeTeam);
            $fixture->setAwayTeam($fixtureDto->awayTeam);
            $fixture->setHomeScore($fixtureDto->homeScore);
            $fixture->setAwayScore($fixtureDto->awayScore);
            $fixture->setStartAt($fixtureDto->startAt);

            $this->entityManager->persist($fixture);

            // TODO: Move to subscriber/dispatcher?
            if ($fixture->getId() !== null && $fixture->hasStarted()) {
                foreach ($fixture->getFixturePredictions() as $prediction) {
                    $points = $this->predictionsService->calculatePoints($prediction);
                    $prediction->setPoints($points);
                    $this->entityManager->persist($prediction);
                }
            }
        }

        $this->entityManager->flush();
    }
}