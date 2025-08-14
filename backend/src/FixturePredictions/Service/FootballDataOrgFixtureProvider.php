<?php

namespace App\FixturePredictions\Service;

use App\Core\Util\DateTimeUtil;
use App\Core\ValueObject\Period;
use App\FixturePredictions\DTO\Provider\FixtureDto;
use App\FixturePredictions\DTO\Provider\SeasonDto;
use App\FixturePredictions\DTO\Provider\TeamDto;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\TeamRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * https://docs.football-data.org/general/v4/index.html
 */
readonly class FootballDataOrgFixtureProvider extends FixtureProvider
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
     * @return Generator<int, TeamDto>
     * @throws Exception|TransportExceptionInterface
     */
    protected function getTeams(Competition $competition, Season $season): Generator
    {
        $responseData = $this->client->getTeams($competition, $season);

        try {
            foreach ($responseData['teams'] as $team) {
                yield $this->createTeamDto($team);
            }
        } catch (Exception $e) {
            throw new Exception('Error while handling data from a provider: ' . $e->getMessage());
        }
    }

    /**
     * https://docs.football-data.org/general/v4/match.html
     *
     * @inheritDoc
     * @return Generator<int, FixtureDto>
     * @throws Exception|TransportExceptionInterface
     */
    protected function getFixtures(
        Competition $competition,
        Season $season,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): Generator {
        $providerSeason = $this->getSeason($competition, $season) ?? throw new Exception('Provider season not found.');
        $batchPeriods = $this->batchSeasonPeriods($providerSeason, $from, $to);

        foreach ($batchPeriods as $period) {
            $responseData = $this->client->getMatches($competition, $season, $period->getStart(), $period->getEnd());

            try {
                foreach ($responseData['matches'] as $match) {
                    yield $this->createFixtureDto($match);
                }
            } catch (Exception $e) {
                throw new Exception('Error while handling data from a provider: ' . $e->getMessage());
            }
        }
    }

    /**
     * https://docs.football-data.org/general/v4/competition.html#_overview
     *
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    private function getSeason(Competition $competition, Season $season): ?SeasonDto
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
     * https://docs.football-data.org/general/v4/match.html#_enums
     */
    private function transformStatus(string $providerStatus): FixtureStatusEnum
    {
        return match ($providerStatus) {
            'SCHEDULED', 'TIMED', 'CANCELLED', 'POSTPONED', 'SUSPENDED' => FixtureStatusEnum::Scheduled,
            'IN_PLAY', 'PAUSED' => FixtureStatusEnum::InPlay,
            'FINISHED', 'AWARDED' => FixtureStatusEnum::Finished,
            default => FixtureStatusEnum::Unknown,
        };
    }

    /**
     * @return Period[]
     * @throws Exception
     */
    private function batchSeasonPeriods(
        SeasonDto $dto,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): array {
        $startDate = null === $from ? $dto->startDate : max($from, $dto->startDate);
        $endDate = null === $to ? $dto->endDate : min($to, $dto->endDate);

        return DateTimeUtil::getPeriods($startDate, $endDate, 'P3M'); // 3 months.
    }

    /**
     * @param Mixed[] $team
     */
    private function createTeamDto(array $team): TeamDto
    {
        return new TeamDto(
            $team['id'],
            $team['shortName'],
        );
    }

    /**
     * @param Mixed[] $match
     * @throws Exception
     */
    private function createFixtureDto(array $match): FixtureDto
    {
        $homeTeam = $this->teamRepository->findOneByProviderTeamId($match['homeTeam']['id'])
            ?? throw new Exception('Provider team not found.');
        $awayTeam = $this->teamRepository->findOneByProviderTeamId($match['awayTeam']['id'])
            ?? throw new Exception('Provider team not found.');

        return new FixtureDto(
            $match['id'],
            $this->transformStatus($match['status']),
            $match['matchday'],
            $homeTeam,
            $awayTeam,
            $match['score']['fullTime']['home'],
            $match['score']['fullTime']['away'],
            new DateTimeImmutable($match['utcDate']),
        );
    }
}
