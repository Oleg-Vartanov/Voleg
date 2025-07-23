<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\DTO\Provider\FixtureDto;
use App\FixturePredictions\DTO\Provider\SeasonDto;
use App\FixturePredictions\DTO\Provider\TeamDto;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\TeamRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
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
     * @throws Exception|TransportExceptionInterface
     */
    public function syncFixtures(Competition $competition, Season $season): void
    {
        $providerSeason = $this->getSeason($competition, $season) ?? throw new Exception('Provider season not found.');
        $batchPeriods = $this->batchSeasonPeriods($providerSeason);

        foreach ($batchPeriods as $period) {
            $fixturesDtos = $this->getFixtures($competition, $season, $period['start'], $period['end']);

            foreach ($fixturesDtos as $fixtureDto) {
                $this->persistFixture($fixtureDto, $competition, $season);
            }
        }

        $this->entityManager->flush();
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
            throw new Exception('Error while handling data from a provider: ' . $e->getMessage());
        }

        return $teamsDtos;
    }

    /**
     * https://docs.football-data.org/general/v4/competition.html#_overview
     *
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getSeason(Competition $competition, Season $season): ?SeasonDto
    {
        $response = $this->client->getSeasons($competition);
        foreach ($response['seasons'] as $providerSeason) {
            if (substr($providerSeason['startDate'], 0, 4) == $season->getYear()) {
                return new SeasonDto(
                    $competition,
                    new DateTimeImmutable($providerSeason['startDate']),
                    new DateTimeImmutable($providerSeason['endDate']),
                );
            }
        }

        return null;
    }

    /**
     * https://docs.football-data.org/general/v4/match.html
     *
     * @inheritDoc
     * @throws Exception|TransportExceptionInterface
     */
    protected function getFixtures(
        Competition $competition,
        Season $season,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
    ): array {
        $responseData = $this->client->getMatches($competition, $season, $from, $to);

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
            throw new Exception('Error while handling data from a provider: ' . $e->getMessage());
        }

        return $fixturesDtos;
    }

    /**
     * https://docs.football-data.org/general/v4/match.html#_enums
     */
    private function transformStatus($providerStatus): FixtureStatusEnum
    {
        return match ($providerStatus) {
            'SCHEDULED', 'TIMED', 'CANCELLED', 'POSTPONED', 'SUSPENDED' => FixtureStatusEnum::Scheduled,
            'IN_PLAY', 'PAUSED' => FixtureStatusEnum::InPlay,
            'FINISHED', 'AWARDED' => FixtureStatusEnum::Finished,
            default => FixtureStatusEnum::Unknown,
        };
    }

    private function batchSeasonPeriods(SeasonDto $dto): array
    {
        $startDate = $dto->startDate;
        $endDate = $dto->endDate;
        $interval = new DateInterval('P3M'); // 3 months.
        $period = new DatePeriod($startDate, $interval, $endDate);
        $dates = iterator_to_array($period);

        if (end($dates) != $endDate) {
            $dates[] = $endDate;
        }

        $batchPeriods = [];
        for ($i = 0; $i < count($dates) - 1; $i++) {
            $batchPeriods[] = [
                'start' => $dates[$i],
                'end' => $dates[$i + 1],
            ];
        }

        return $batchPeriods;
    }
}
