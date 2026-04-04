<?php

namespace App\FixturePredictions\Test\Functional;

use App\Core\Test\Trait\ContainerTestTrait;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Exception\FixtureProviderClientException;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Repository\TeamRepository;
use App\FixturePredictions\Service\FootballDataOrgFixtureProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[TestDox('Fixture Predictions')]
class FootballDataOrgFixturesProviderTest extends KernelTestCase
{
    use ContainerTestTrait;

    private Stub&HttpClientInterface $httpClientStub;
    private FootballDataOrgFixtureProvider $fixturesProvider;
    private FixtureRepository $fixtureRepository;
    private CompetitionRepository $competitionRepository;
    private SeasonRepository $seasonRepository;
    private TeamRepository $teamRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->httpClientStub = $this->createStub(HttpClientInterface::class);
        static::getContainer()->set(HttpClientInterface::class, $this->httpClientStub);

        $this->fixturesProvider = $this->getService(FootballDataOrgFixtureProvider::class);
        $this->teamRepository = $this->getService(TeamRepository::class);
        $this->fixtureRepository = $this->getService(FixtureRepository::class);
        $this->competitionRepository = $this->getService(CompetitionRepository::class);
        $this->seasonRepository = $this->getService(SeasonRepository::class);
    }

    /**
     * @throws Exception
     */
    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Provider: sync success')]
    public function testSyncSuccess(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->prepareProviderResponses([
            ['statusCode' => 200, 'content' => $this->teamsResponseContent()],
            ['statusCode' => 200, 'content' => $this->seasonResponseContent()],
            ['statusCode' => 200, 'content' => $this->fixturesResponseContent()],
        ]);
        $this->fixturesProvider->sync($competition, $season);

        $teams = $this->teamRepository->findByProviderTeamIds([57, 58, 61]);
        self::assertCount(3, $teams);

        $fixtures = $this->fixtureRepository->findByProviderFixtureIds([537793, 537794]);
        self::assertCount(2, $fixtures);
    }

    /**
     * @throws Exception
     */
    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Provider: sync error invalid response data')]
    public function testSyncErrorInvalidData(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->prepareProviderResponses([
            ['statusCode' => 200, 'content' => 'invalid-data'],
        ]);
        self::expectException(FixtureProviderClientException::class);
        self::expectExceptionMessage('Invalid API response: expected array.');
        $this->fixturesProvider->sync($competition, $season);
    }

    /**
     * @throws Exception
     */
    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Provider: sync error fetching teams')]
    public function testSyncErrorFetchingTeams(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->prepareProviderResponses([
            ['statusCode' => 500, 'content' => 'test'],
        ]);
        self::expectException(FixtureProviderClientException::class);
        self::expectExceptionMessage('Failed to fetch teams.');
        $this->fixturesProvider->sync($competition, $season);
    }

    /**
     * @throws Exception
     */
    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Provider: sync error fetching seasons')]
    public function testSyncErrorFetchingSeasons(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->prepareProviderResponses([
            ['statusCode' => 200, 'content' => $this->teamsResponseContent()],
            ['statusCode' => 500, 'content' => 'test'],
        ]);
        self::expectException(FixtureProviderClientException::class);
        self::expectExceptionMessage('Failed to fetch seasons.');
        $this->fixturesProvider->sync($competition, $season);
    }

    /**
     * @throws Exception
     */
    #[AllowMockObjectsWithoutExpectations]
    #[TestDox('Provider: sync error fetching matches')]
    public function testSyncErrorFetchingMatches(): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->prepareProviderResponses([
            ['statusCode' => 200, 'content' => $this->teamsResponseContent()],
            ['statusCode' => 200, 'content' => $this->seasonResponseContent()],
            ['statusCode' => 500, 'content' => 'test'],
        ]);
        self::expectException(FixtureProviderClientException::class);
        self::expectExceptionMessage('Failed to fetch matches.');
        $this->fixturesProvider->sync($competition, $season);
    }

    /**
     * @throws Exception
     */
    private function prepareProviderResponses(array $responses): void
    {
        $stubResponses = [];

        foreach ($responses as $response) {
            $stubResponse = $this->createStub(ResponseInterface::class);
            $stubResponse->method('getStatusCode')->willReturn($response['statusCode']);
            $stubResponse->method('getContent')->willReturn(json_encode($response['content']));
            $stubResponses[] = $stubResponse;
        }

        $this->httpClientStub
            ->method('request')
            ->willReturnOnConsecutiveCalls(...$stubResponses);
    }

    private function teamsResponseContent(): array
    {
        return [
            'teams' => [
                ['id' => 57, 'shortName' => 'Arsenal'],
                ['id' => 58, 'shortName' => 'Aston Villa'],
                ['id' => 61, 'shortName' => 'Chelsea'],
            ]
        ];
    }

    private function seasonResponseContent(): array
    {
        return [
            'seasons' => [
                ['startDate' => '2025-01-01T00:00:00Z', 'endDate' => '2025-01-30T00:00:00Z'],
            ]
        ];
    }

    private function fixturesResponseContent(): array
    {
        return [
            'matches' => [
                [
                    'id' => 537793,
                    'status' => 'FINISHED',
                    'matchday' => 1,
                    'utcDate' => '2025-01-01T15:30:00Z',
                    'homeTeam' => ['id' => 57],
                    'awayTeam' => ['id' => 58],
                    'score' => ['fullTime' => ['home' => 1, 'away' => 2]],
                ],
                [
                    'id' => 537794,
                    'status' => 'FINISHED',
                    'matchday' => 2,
                    'utcDate' => '2025-01-02T15:30:00Z',
                    'homeTeam' => ['id' => 58],
                    'awayTeam' => ['id' => 61],
                    'score' => ['fullTime' => ['home' => 1, 'away' => 2]],
                ],
            ]
        ];
    }
}
