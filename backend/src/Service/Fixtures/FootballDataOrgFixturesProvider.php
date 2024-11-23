<?php

namespace App\Service\Fixtures;

use App\DTO\Fixtures\Provider\FixtureDto;
use App\DTO\Fixtures\Provider\TeamDto;
use App\Entity\Competition;
use App\Entity\Season;
use App\Enum\Fixtures\FixtureStatusEnum;
use App\Repository\FixtureRepository;
use App\Repository\TeamRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * https://docs.football-data.org/general/v4/index.html
 */
readonly class FootballDataOrgFixturesProvider extends AbstractFixturesProvider
{
    public function __construct(
        TeamRepository $teamRepository,
        FixtureRepository $fixtureRepository,
        EntityManagerInterface $entityManager,
        PredictionsService $predictionsService,
        protected FootballDataOrgClient $client,
    ) {
        parent::__construct($teamRepository, $fixtureRepository, $entityManager, $predictionsService);
    }

    /**
     * https://docs.football-data.org/general/v4/team.html
     *
     * @inheritDoc
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    protected function getTeams(Competition $competition, Season $season): array
    {
        $responseData = $this->client->getTeams($competition, $season);

        try {
            $teamsDtos = [];
            foreach ($responseData['teams'] as $team) {
                $dto = new TeamDto();

                $dto->name = $team['shortName'];
                $dto->providerTeamId = $team['id'];

                $teamsDtos[] = $dto;
            }
        } catch (Exception $e) {
            throw new Exception('Error while handling data from a provider: '.$e->getMessage());
        }

        return $teamsDtos;
    }

    /**
     * https://docs.football-data.org/general/v4/match.html
     *
     * @inheritDoc
     * @throws Exception|TransportExceptionInterface
     */
    protected function getFixtures(Competition $competition, Season $season): array
    {
        $responseData = $this->client->getMatches($competition, $season);

        try {
            $fixturesDtos = [];
            foreach ($responseData['matches'] as $match) {
                $dto = new FixtureDto();

                $dto->providerFixtureId = $match['id'];
                $dto->status = $this->transformStatus($match['status']);
                $dto->matchday = $match['matchday'];
                $dto->homeTeam = $this->teamRepository->findOneByProviderTeamId($match['homeTeam']['id']);
                $dto->awayTeam = $this->teamRepository->findOneByProviderTeamId($match['awayTeam']['id']);
                $dto->homeScore = $match['score']['fullTime']['home'];
                $dto->awayScore = $match['score']['fullTime']['away'];
                $dto->startAt = new DateTimeImmutable($match['utcDate']);

                $fixturesDtos[] = $dto;
            }
        } catch (Exception $e) {
            throw new Exception('Error while handling data from a provider: '.$e->getMessage());
        }

        return $fixturesDtos;
    }

    /**
     * https://docs.football-data.org/general/v4/match.html#_enums
     */
    private function transformStatus($providerStatus): FixtureStatusEnum
    {
        return match ($providerStatus) {
            'SCHEDULED' => FixtureStatusEnum::Scheduled,
            'IN_PLAY' => FixtureStatusEnum::InPlay,
            'FINISHED' => FixtureStatusEnum::Finished,
            default => FixtureStatusEnum::Unknown,
        };
    }
}