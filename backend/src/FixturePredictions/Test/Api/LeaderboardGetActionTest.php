<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class LeaderboardGetActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Leaderboard: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $response = $this->sendRequest();
        self::assertResponseIsSuccessful();

        $content = json_decode($response->getContent(), true);

        self::assertSame([
            'start' => '2025-01-01',
            'end' => '2025-01-02',
            'competition' => CompetitionCodeEnum::EPL->value,
            'season' => 2024,
            'limit' => 20,
        ], $content['filters']);
        self::assertNotEmpty($content['users']);
    }

    #[TestDox('Leaderboard: validation error')]
    public function testValidationError(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $this->sendRequest('invalid-date');
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
        string $end = '2025-01-02',
        int $season = 2024,
    ): Response {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_leaderboard', [
                'start' => $start,
                'end' => $end,
                'season' => $season,
                'limit' => 20,
            ]),
        );

        return $this->client->getResponse();
    }
}
