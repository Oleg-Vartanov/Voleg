<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class MakePredictionsActionTest extends ApiTestCase
{
    #[TestDox('Make predictions: success')]
    public function testSuccess(): void
    {
        $fixtureRepository = $this->getService(FixtureRepository::class);
        $fptRepository = $this->getService(FixturePredictionRepository::class);

        $user = $this->signIn($this->createUser());

        $fixtures = $fixtureRepository->findBy(['id' => [1, 2]]);
        foreach ($fixtures as $fixture) {
            $fixture->setStartAt(new DateTimeImmutable('+1 day'));
        }
        $fixtureRepository->flush();

        $this->sendRequest([
            ['fixtureId' => 1, 'homeScore' => 10, 'awayScore' => 11],
            ['fixtureId' => 2, 'homeScore' => 12, 'awayScore' => 13],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $fp1 = $fptRepository->findOneBy(['user' => $user, 'fixture' => $fixtures[0]]);
        $fp2 = $fptRepository->findOneBy(['user' => $user, 'fixture' => $fixtures[1]]);
        self::assertSame(10, $fp1->getHomeScore());
        self::assertSame(11, $fp1->getAwayScore());
        self::assertSame(12, $fp2->getHomeScore());
        self::assertSame(13, $fp2->getAwayScore());
    }

    #[TestDox('Make predictions: fixture has already started error')]
    public function testFixtureHasAlreadyStartedError(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest([['fixtureId' => 3, 'homeScore' => 1, 'awayScore' => 1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    #[TestDox('Make predictions: not found')]
    public function testNotFound(): void
    {
        $this->signIn($this->createUser());
        $nonExistentId = 1000;
        $this->sendRequest([['fixtureId' => $nonExistentId, 'homeScore' => 1, 'awayScore' => 1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Make predictions: validation error')]
    public function testValidationError(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest([['fixtureId' => -1, 'homeScore' => -1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Make predictions: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(array $content = []): void
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('make_predictions'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        $this->client->getResponse();
    }
}
