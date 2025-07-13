<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class PredictionsGetActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Predictions GET: success')]
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
        ], $content['filters']);
        self::assertNotEmpty($content['fixtures']);
    }

    #[TestDox('Predictions GET: validation error')]
    public function testValidationError(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $this->sendRequest('invalid-date');
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Predictions GET: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(
        string $start = '2025-01-01',
        string $end = '2025-01-02',
        int $year = 2024,
    ): Response {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_predictions', [
                'start' => $start,
                'end' => $end,
                'year' => $year,
            ]),
        );

        return $this->client->getResponse();
    }
}
