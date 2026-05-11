<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\DataFixture\SeasonFixture;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class PredictionsGetActionTest extends ApiTestCase
{
    #[TestDox('Predictions GET: success')]
    public function testSuccess(): void
    {
        $user = $this->signIn($this->createUser());

        $response = $this->sendRequest(userIds: [$user->getId()]);
        self::assertResponseIsSuccessful();

        $content = json_decode($response->getContent(), true);
        $filters = $content['filters'];
        self::assertSame('2025-01-01', $filters['start']);
        self::assertSame('2025-01-02', $filters['end']);
        self::assertSame(CompetitionCodeEnum::EPL->value, $filters['competition']);
        self::assertSame(SeasonFixture::CURRENT_SEASON, $filters['season']);
        self::assertSame(20, $filters['limit']);
        self::assertSame($user->getId(), array_first($filters['users'])['id']);
        self::assertSame(20, count($content['fixtures']));
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
    ): Response {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('fixtures_predictions', [
                'start' => '2025-01-01',
                'end' => '2025-01-02',
                'season' => SeasonFixture::CURRENT_SEASON,
                'limit' => 20,
                'userIds' => $userIds,
            ]),
        );

        return $this->client->getResponse();
    }
}
