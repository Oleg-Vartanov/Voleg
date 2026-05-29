<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionPostActionTest extends ApiTestCase
{
    private SeConnectionRepository $conRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->conRepo = $this->getService(SeConnectionRepository::class);
    }

    #[TestDox('Connection POST: success')]
    public function testSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->signIn($userA);
        $this->sendRequest($userB->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->getResponseData();
        self::assertNotNull($this->conRepo->find($data['id']));
        self::assertEquals($userA->getId(), $data['userA']['id']);
        self::assertEquals($userA->getId(), $data['requestedBy']['id']);
        self::assertEquals($userB->getId(), $data['userB']['id']);
        self::assertEquals(
            SeConnectionStatusEnum::PENDING->value,
            $data['status']
        );
    }

    #[TestDox('Connection POST: requested user not found')]
    public function testRequestedUserNotFound(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest(999999999);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Connection POST: same user error')]
    public function testSameUserError(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($user->getId());
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[TestDox('Connection POST: already exists error')]
    public function testAlreadyExistsError(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();
        $this->conRepo->save(new SeConnection($userA, $userB), true);
        $this->signIn($userA);
        $this->sendRequest($userB->getId());
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function sendRequest(int $connectionUserId): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('se_connection_post'),
            parameters: ['connectionUserId' => $connectionUserId],
        );
    }
}
