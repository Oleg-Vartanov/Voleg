<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\User\Test\Trait\UserTestTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class MakePredictionsActionTest extends ApiTestCase
{
    use UserTestTrait;

    private EntityManagerInterface $em;
    private FixtureRepository $fRepository;
    private FixturePredictionRepository $fpRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();

        $container = $this->getContainer();
        $this->fRepository = $container->get(FixtureRepository::class);
        $this->fpRepository = $container->get(FixturePredictionRepository::class);
        $this->em = $container->get(EntityManagerInterface::class);
    }

    #[TestDox('Make predictions: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $fixtures = $this->fRepository->findBy(['id' => [1, 2]]);
        foreach ($fixtures as $fixture) {
            $fixture->setStartAt(new DateTimeImmutable('+1 day'));
        }
        $this->em->flush();

        $this->sendRequest([
            ['fixtureId' => 1, 'homeScore' => 10, 'awayScore' => 11],
            ['fixtureId' => 2, 'homeScore' => 12, 'awayScore' => 13],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $fp1 = $this->fpRepository->findOneBy(['user' => $user, 'fixture' => $fixtures[0]]);
        $fp2 = $this->fpRepository->findOneBy(['user' => $user, 'fixture' => $fixtures[1]]);
        self::assertSame($fp1->getHomeScore(), 10);
        self::assertSame($fp1->getAwayScore(), 11);
        self::assertSame($fp2->getHomeScore(), 12);
        self::assertSame($fp2->getAwayScore(), 13);
    }

    #[TestDox('Make predictions: fixture has already started error')]
    public function testFixtureHasAlreadyStartedError(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $this->sendRequest([['fixtureId' => 3, 'homeScore' => 1, 'awayScore' => 1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    #[TestDox('Make predictions: not found')]
    public function testNotFound(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $nonExistentId = 1000;
        $this->sendRequest([['fixtureId' => $nonExistentId, 'homeScore' => 1, 'awayScore' => 1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Make predictions: validation error')]
    public function testValidationError(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $this->sendRequest([['fixtureId' => -1, 'homeScore' => -1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Make predictions: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('make_predictions'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}
