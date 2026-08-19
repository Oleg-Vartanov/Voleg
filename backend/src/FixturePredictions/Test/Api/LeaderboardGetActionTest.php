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

        self::assertSame([
            'start' => '2025-01-01',
            'end' => '2025-01-02',
            'competition' => CompetitionCodeEnum::EPL->value,
            'season' => SeasonSeeder::CURRENT_SEASON_YEAR,
            'limit' => 20,
        ], $data['filters']);
        self::assertNotEmpty($data['users']);
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
    ): void {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_leaderboard', [
                'start' => $start,
                'end' => '2025-01-02',
                'season' => SeasonSeeder::CURRENT_SEASON_YEAR,
                'limit' => 20,
            ]),
        );
    }
}
