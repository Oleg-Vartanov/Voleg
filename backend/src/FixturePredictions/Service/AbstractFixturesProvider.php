<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\DTO\Provider\FixtureDto;
use App\FixturePredictions\DTO\Provider\TeamDto;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Interface\FixturesProviderInterface;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\TeamRepository;
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

    abstract public function syncFixtures(Competition $competition, Season $season): void;

    /**
     * @return TeamDto[]
     */
    abstract protected function getTeams(Competition $competition, Season $season): array;

    /**
     * @throws Exception
     */
    public function syncTeams(Competition $competition, Season $season): void
    {
        $teamsDtos = $this->getTeams($competition, $season);

        foreach ($teamsDtos as $teamDto) {
            $this->persistTeam($teamDto);
        }

        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    protected function persistFixture(FixtureDto $dto, Competition $competition, Season $season): void
    {
        $fixture = $this->fixtureRepository->findOneByProviderFixtureId($dto->providerFixtureId);

        if ($fixture === null) {
            $fixture = new Fixture();
            $fixture->setProviderFixtureId($dto->providerFixtureId);
        }
        $fixture->setCompetition($competition);
        $fixture->setSeason($season);
        $fixture->setStatus($dto->status);
        $fixture->setMatchday($dto->matchday);
        $fixture->setHomeTeam($dto->homeTeam);
        $fixture->setAwayTeam($dto->awayTeam);
        $fixture->setHomeScore($dto->homeScore);
        $fixture->setAwayScore($dto->awayScore);
        $fixture->setStartAt($dto->startAt);

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

    /**
     * @throws Exception
     */
    private function persistTeam(TeamDto $dto): void
    {
        $team = $this->teamRepository->findOneByProviderTeamId($dto->providerTeamId);

        if ($team !== null) {
            return;
        }

        $team = new Team();
        $team->setName($dto->name);
        $team->setProviderTeamId($dto->providerTeamId);

        $this->entityManager->persist($team);
    }
}
