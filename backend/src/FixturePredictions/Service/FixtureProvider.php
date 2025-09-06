<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\DTO\Provider\FixtureDto;
use App\FixturePredictions\DTO\Provider\TeamDto;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\TeamRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

abstract readonly class FixtureProvider
{
    public function __construct(
        protected TeamRepository $teamRepository,
        protected FixtureRepository $fixtureRepository,
        protected EntityManagerInterface $entityManager,
        protected PredictionsService $predictionsService,
    ) {
    }

    /**
     * Get teams from a provider
     *
     * @return Generator<int, TeamDto>
     */
    abstract protected function getTeams(Competition $competition, Season $season): Generator;

    /**
     * Get fixtures from a provider
     *
     * @return Generator<int, FixtureDto>
     */
    abstract protected function getFixtures(
        Competition $competition,
        Season $season,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): Generator;

    public function sync(
        Competition $competition,
        Season $season,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): void {
        $this->syncTeams($competition, $season);
        $this->syncFixtures($competition, $season, $from, $to);
    }

    private function syncTeams(Competition $competition, Season $season): void
    {
        $teamsDtos = $this->getTeams($competition, $season);

        foreach ($teamsDtos as $teamDto) {
            $this->persistTeam($teamDto);
        }

        $this->entityManager->flush();
    }

    private function syncFixtures(
        Competition $competition,
        Season $season,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): void {
        $fixturesDtos = $this->getFixtures($competition, $season, $from, $to);

        foreach ($fixturesDtos as $fixturesDto) {
            $this->persistFixture($fixturesDto, $competition, $season);
        }

        $this->entityManager->flush();
    }

    /**
     * @throws ExceptionInterface
     */
    private function persistFixture(FixtureDto $dto, Competition $competition, Season $season): void
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

        $this->predictionsService->dispatchUpdatePoints($fixture);
    }

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
