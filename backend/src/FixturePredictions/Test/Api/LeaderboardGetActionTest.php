<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\DataFixture\SeasonFixture;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class LeaderboardGetActionTest extends ApiTestCase
{
    #[TestDox('Leaderboard: success')]
    public function testSuccess(): void
    {
        $this->signIn($this->createUser());

        $this->sendRequest();
        self::assertResponseIsSuccessful();

        $data = $this->getResponseData();

        self::assertSame([
            'start' => '2025-01-01',
            'end' => '2025-01-02',
            'competition' => CompetitionCodeEnum::EPL->value,
            'season' => SeasonFixture::CURRENT_SEASON,
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
                'season' => SeasonFixture::CURRENT_SEASON,
                'limit' => 20,
            ]),
        );
    }
}
