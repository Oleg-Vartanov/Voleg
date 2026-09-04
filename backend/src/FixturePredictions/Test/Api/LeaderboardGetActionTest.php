<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Test\Trait\FootballFixtureTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class LeaderboardGetActionTest extends ApiTestCase
{
    use FootballFixtureTestTrait;

    #[TestDox('Leaderboard: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser(flush: false);
        $fixture = $this->createFootballFixture();
        $this->createFootballPrediction($user, $fixture, points: 3);

        $this->signIn($user);
        $this->sendRequest();
        self::assertResponseIsSuccessful();

        $data = $this->getResponseData();
        $filters = $data['filters'];

        self::assertSame('2025-01-01', $filters['start']);
        self::assertSame('2025-01-02', $filters['end']);
        self::assertSame(CompetitionCodeEnum::EPL->value, $filters['competition']);
        self::assertSame(SeasonSeeder::CURRENT_SEASON_YEAR, $filters['season']);
        self::assertSame(20, $filters['limit']);
        self::assertSame(0, $filters['offset']);
        self::assertGreaterThanOrEqual(1, $filters['total']);
        self::assertSame(
            (string) $filters['total'],
            $this->client->getResponse()->headers->get('X-Total-Count'),
        );
        self::assertNotEmpty($data['users']);
    }

    #[TestDox('Leaderboard: pages through users and reports the full total')]
    public function testPagination(): void
    {
        $fixture = $this->createFootballFixture(
            providerFixtureId: 700000 + random_int(0, 99999),
        );
        $users = [];
        foreach (range(1, 5) as $rank) {
            $user = $this->createUser();
            // Distinct totals so order is stable across pages.
            $this->createFootballPrediction($user, $fixture, points: 10 - $rank);
            $users[] = $user;
        }
        $this->signIn($users[0]);

        $this->sendRequest(limit: 2);
        self::assertResponseIsSuccessful();

        $firstPage = $this->getResponseData();
        self::assertGreaterThanOrEqual(5, $firstPage['filters']['total']);
        self::assertSame(
            (string) $firstPage['filters']['total'],
            $this->client->getResponse()->headers->get('X-Total-Count'),
        );
        self::assertCount(2, $firstPage['users']);

        $this->sendRequest(limit: 2, offset: 2);
        self::assertResponseIsSuccessful();

        $secondPage = $this->getResponseData();
        self::assertSame(2, $secondPage['filters']['offset']);
        self::assertCount(2, $secondPage['users']);

        $ids = array_merge(
            array_column(array_column($firstPage['users'], 'user'), 'id'),
            array_column(array_column($secondPage['users'], 'user'), 'id'),
        );
        self::assertSame($ids, array_unique($ids));
    }

    #[TestDox('Leaderboard: negative offset is rejected')]
    public function testNegativeOffsetIsRejected(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest(offset: -1);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Leaderboard: validation error')]
    public function testValidationError(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest(start: 'invalid-date');
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Leaderboard: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(
        string $start = '2025-01-01',
        int $limit = 20,
        int $offset = 0,
    ): void {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_leaderboard', [
                'start' => $start,
                'end' => '2025-01-02',
                'season' => SeasonSeeder::CURRENT_SEASON_YEAR,
                'limit' => $limit,
                'offset' => $offset,
            ]),
        );
    }
}
