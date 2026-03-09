<?php

namespace App\FixturePredictions\Test\Functional;

use App\Core\Test\Trait\ContainerTestTrait;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Repository\TeamRepository;
use App\FixturePredictions\Service\FootballDataOrgFixtureProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[TestDox('Fixture Predictions')]
class FootballDataOrgFixturesProviderTest extends KernelTestCase
{
    use ContainerTestTrait;

    private MockObject&HttpClientInterface $httpClientMock;
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
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        static::getContainer()->set(HttpClientInterface::class, $this->httpClientMock);

        $this->fixturesProvider = $this->getService(FootballDataOrgFixtureProvider::class);
        $this->teamRepository = $this->getService(TeamRepository::class);
        $this->fixtureRepository = $this->getService(FixtureRepository::class);
        $this->competitionRepository = $this->getService(CompetitionRepository::class);
        $this->seasonRepository = $this->getService(SeasonRepository::class);
    }

    /**
     * @throws Exception
     */
    #[TestDox('Provider: sync success')]
    public function testSync(): void
    {
        $this->prepareProviderResponses([
            ['statusCode' => 200, 'content' => $this->mockTeamsResponse()],
            ['statusCode' => 200, 'content' => $this->mockSeasonResponse()],
            ['statusCode' => 200, 'content' => $this->mockFixturesResponse()],
        ]);

        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        $season = $this->seasonRepository->findOneByYear(2025);

        $this->fixturesProvider->sync($competition, $season);

        $teams = $this->teamRepository->findByProviderTeamIds([57, 58, 61]);
        self::assertCount(3, $teams);

        $fixtures = $this->fixtureRepository->findByProviderFixtureIds([537793, 537794]);
        self::assertCount(2, $fixtures);
    }

    /**
     * @throws Exception
     */
    private function prepareProviderResponses(array $responses): void
    {
        $mockedResponses = [];

        foreach ($responses as $response) {
            $responseMock = $this->createMock(ResponseInterface::class);
            $responseMock->method('getStatusCode')->willReturn($response['statusCode']);
            $responseMock->method('getContent')->willReturn(json_encode($response['content']));
            $mockedResponses[] = $responseMock;
        }

        $this->httpClientMock
            ->method('request')
            ->willReturnOnConsecutiveCalls(...$mockedResponses);
    }

    private function mockTeamsResponse(): array
    {
        return [
            'teams' => [
                ['id' => 57, 'shortName' => 'Arsenal'],
                ['id' => 58, 'shortName' => 'Aston Villa'],
                ['id' => 61, 'shortName' => 'Chelsea'],
            ]
        ];
    }

    private function mockSeasonResponse(): array
    {
        return [
            'seasons' => [
                ['startDate' => '2025-01-01T00:00:00Z', 'endDate' => '2025-01-30T00:00:00Z'],
            ]
        ];
    }

    private function mockFixturesResponse(): array
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
