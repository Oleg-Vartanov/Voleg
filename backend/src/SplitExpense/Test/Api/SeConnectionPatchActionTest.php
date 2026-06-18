<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionPatchActionTest extends ApiTestCase
{
    private UserRepository $userRepo;
    private SeConnectionRepository $conRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->getService(UserRepository::class);
        $this->conRepo = $this->getService(SeConnectionRepository::class);
    }

    #[TestDox('Connection PATCH: accept success')]
    public function testAcceptSuccess(): void
    {
        $userA = $this->userRepo->findByTag('user1');
        $userB = $this->userRepo->findByTag('user2');
        $connection = $this->conRepo->findOneByUsers($userA, $userB);
        self::assertNotNull($connection);
        self::assertSame(SeConnectionStatusEnum::PENDING, $connection->getStatus());

        $this->signIn($userB);
        $this->sendRequest($connection->getId(), SeConnectionStatusEnum::ACCEPTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            SeConnectionStatusEnum::ACCEPTED->value,
            $this->getResponseData()['status']
        );
    }

    #[TestDox('Connection PATCH: reject success')]
    public function testRejectSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();
        $connection = new SeConnection($userA, $userB);
        $this->conRepo->save($connection, true);

        $this->signIn($userB);
        $this->sendRequest($connection->getId(), SeConnectionStatusEnum::REJECTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            SeConnectionStatusEnum::REJECTED->value,
            $this->getResponseData()['status']
        );
    }

    #[TestDox('Connection PATCH: only requested user can respond')]
    public function testOnlyRequestedUserCanRespond(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();
        $connection = new SeConnection($userA, $userB);
        $this->conRepo->save($connection, true);

        $this->signIn($userA);
        $this->sendRequest($connection->getId(), SeConnectionStatusEnum::ACCEPTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function sendRequest(int $id, string $status): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_PATCH,
            uri: $this->router->generate('se_connection_patch', ['id' => $id]),
            parameters: ['status' => $status],
        );
    }
}
