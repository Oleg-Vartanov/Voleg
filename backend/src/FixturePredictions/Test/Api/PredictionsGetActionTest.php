<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Test\Trait\FootballFixtureTestTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class PredictionsGetActionTest extends ApiTestCase
{
    use FootballFixtureTestTrait;

    #[TestDox('Predictions GET: success')]
    public function testSuccess(): void
    {
        $this->createFootballFixtures(20);
        $user = $this->signIn($this->createUser());

        $this->sendRequest(userIds: [$user->getId()]);
        self::assertResponseIsSuccessful();

        $data = $this->getResponseData();
        $filters = $data['filters'];
        self::assertSame('2025-01-01', $filters['start']);
        self::assertSame('2025-01-02', $filters['end']);
        self::assertSame(CompetitionCodeEnum::EPL->value, $filters['competition']);
        self::assertSame(SeasonSeeder::CURRENT_SEASON_YEAR, $filters['season']);
        self::assertSame(20, $filters['limit']);
        self::assertSame(0, $filters['offset']);
        self::assertSame($user->getId(), array_first($filters['users'])['id']);
        self::assertSame(20, count($data['fixtures']));
        // The test database is shared, so only the lower bound of the total is predictable.
        self::assertGreaterThanOrEqual(20, $filters['total']);
        self::assertSame(
            (string) $filters['total'],
            $this->client->getResponse()->headers->get('X-Total-Count'),
        );
    }

    #[TestDox('Predictions GET: pages through fixtures and reports the full total')]
    public function testPagination(): void
    {
        // Unique window per run — the test database is shared and not wiped between suites.
        $year = 2100 + random_int(0, 50);
        $start = sprintf('%d-03-01', $year);
        $end = sprintf('%d-03-05', $year);
        $baseId = 500000 + random_int(0, 400000);

        foreach (range(0, 4) as $day) {
            $this->createFootballFixture(
                startAt: new DateTimeImmutable(sprintf('%d-03-0%d', $year, $day + 1)),
                providerFixtureId: $baseId + $day,
            );
        }
        $this->signIn($this->createUser());

        $this->sendRequest(start: $start, end: $end, limit: 2);
        self::assertResponseIsSuccessful();

        $firstPage = $this->getResponseData();
        self::assertSame(5, $firstPage['filters']['total']);
        self::assertSame('5', $this->client->getResponse()->headers->get('X-Total-Count'));
        self::assertCount(2, $firstPage['fixtures']);

        $this->sendRequest(start: $start, end: $end, limit: 2, offset: 2);
        self::assertResponseIsSuccessful();

        $secondPage = $this->getResponseData();
        self::assertSame(5, $secondPage['filters']['total']);
        self::assertSame(2, $secondPage['filters']['offset']);
        self::assertCount(2, $secondPage['fixtures']);

        // The last page is partial, and no fixture may repeat across pages.
        $this->sendRequest(start: $start, end: $end, limit: 2, offset: 4);
        self::assertResponseIsSuccessful();

        $lastPage = $this->getResponseData();
        self::assertCount(1, $lastPage['fixtures']);

        $ids = array_merge(
            array_column($firstPage['fixtures'], 'id'),
            array_column($secondPage['fixtures'], 'id'),
            array_column($lastPage['fixtures'], 'id'),
        );
        self::assertSame($ids, array_unique($ids));
    }

    #[TestDox('Predictions GET: limit applies to fixtures, not to prediction rows')]
    public function testLimitCountsFixturesWhenPredictionsAreFetchJoined(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $year = 2200 + random_int(0, 50);
        $start = sprintf('%d-06-01', $year);
        $end = sprintf('%d-06-04', $year);
        $baseId = 900000 + random_int(0, 90000);

        foreach (range(0, 3) as $day) {
            $fixture = $this->createFootballFixture(
                startAt: new DateTimeImmutable(sprintf('%d-06-0%d', $year, $day + 1)),
                providerFixtureId: $baseId + $day,
            );
            $this->createFootballPrediction($user, $fixture);
            $this->createFootballPrediction($otherUser, $fixture);
        }
        $this->signIn($user);

        $this->sendRequest(
            userIds: [$user->getId(), $otherUser->getId()],
            start: $start,
            end: $end,
            limit: 3,
        );
        self::assertResponseIsSuccessful();

        $data = $this->getResponseData();
        self::assertSame(4, $data['filters']['total']);
        self::assertCount(3, $data['fixtures']);
    }

    #[TestDox('Predictions GET: negative offset is rejected')]
    public function testNegativeOffsetIsRejected(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest(offset: -1);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Predictions GET: validation error')]
    public function testValidationError(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest(userIds: ['invalid-id']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Predictions GET: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(
        array $userIds = [],
        string $start = '2025-01-01',
        string $end = '2025-01-02',
        int $limit = 20,
        int $offset = 0,
    ): void {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_predictions', [
                'start' => $start,
                'end' => $end,
                'season' => SeasonSeeder::CURRENT_SEASON_YEAR,
                'limit' => $limit,
                'offset' => $offset,
                'userIds' => $userIds,
            ]),
        );
    }
}
