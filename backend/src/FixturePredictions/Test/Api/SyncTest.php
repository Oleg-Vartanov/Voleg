<?php

namespace App\FixturePredictions\Test\Api;

use App\Core\Test\ApiTestCase;
use App\FixturePredictions\Service\FixtureProvider;
use App\User\Enum\RoleEnum;
use App\User\Test\Trait\UserTestTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Fixture Predictions')]
class SyncTest extends ApiTestCase
{
    use UserTestTrait;

    private FixtureProvider $fixturesProviderMock;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();

        $this->client->disableReboot(); // For mocks in a controller.
        $this->fixturesProviderMock = $this->createMock(FixtureProvider::class);
        static::getContainer()->set(FixtureProvider::class, $this->fixturesProviderMock);
    }

    #[TestDox('Sync request: success')]
    public function testSuccess(): void
    {
        $anyDate = (new DateTimeImmutable())->format('Y-m-d\TH:i:sO');

        $this->fixturesProviderMock
            ->expects(self::once())
            ->method('sync');

        $user = $this->createUser(['roles' => [RoleEnum::ROLE_ADMIN->value]]);
        $this->signIn($user);

        $this->sendRequest([
            'competitionCode' => 'PL',
            'seasonYear' => 2000,
            'from' => $anyDate,
            'to' => $anyDate,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[TestDox('Sync request: not found')]
    public function testNotFound(): void
    {
        $anyDate = (new DateTimeImmutable())->format('Y-m-d\TH:i:sO');

        $user = $this->createUser(['roles' => [RoleEnum::ROLE_ADMIN->value]]);
        $this->signIn($user);

        $this->sendRequest([
            'competitionCode' => 'PL',
            'seasonYear' => 1970,
            'from' => $anyDate,
            'to' => $anyDate,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Sync request: validation error')]
    public function testValidationError(): void
    {
        $user = $this->createUser(['roles' => [RoleEnum::ROLE_ADMIN->value]]);
        $this->signIn($user);

        $this->sendRequest([
            ['competitionCode' => 'InvalidCode', 'seasonYear' => -1]]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Sync request: access denied')]
    public function testUserDeleteAccessDenied(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest();

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Sync request: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('fixtures_sync'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}
