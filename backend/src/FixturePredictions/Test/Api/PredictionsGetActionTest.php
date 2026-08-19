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
        self::assertSame($user->getId(), array_first($filters['users'])['id']);
        self::assertSame(20, count($data['fixtures']));
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
    ): void {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_predictions', [
                'start' => '2025-01-01',
                'end' => '2025-01-02',
                'season' => SeasonSeeder::CURRENT_SEASON_YEAR,
                'limit' => 20,
                'userIds' => $userIds,
            ]),
        );
    }
}
