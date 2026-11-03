<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\Connection;
use App\SplitExpense\Enum\ConnectionStatusEnum;
use App\SplitExpense\Repository\ConnectionRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class ConnectionResponseActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private ConnectionRepository $conRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->conRepo = $this->getService(ConnectionRepository::class);
    }

    #[TestDox('Connection response: accept success')]
    public function testAcceptSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser(flush: false);
        $connection = $this->createConnection($userA, $userB, ConnectionStatusEnum::PENDING);
        self::assertSame(ConnectionStatusEnum::PENDING, $connection->getStatus());

        $this->signIn($userB);
        $this->sendRequest($connection->getId(), ConnectionStatusEnum::ACCEPTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            ConnectionStatusEnum::ACCEPTED->value,
            $this->getResponseData()['status']
        );
    }

    #[TestDox('Connection response: reject success')]
    public function testRejectSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();
        $connection = new Connection($userA, $userB);
        $this->conRepo->save($connection, true);

        $this->signIn($userB);
        $this->sendRequest($connection->getId(), ConnectionStatusEnum::REJECTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            ConnectionStatusEnum::REJECTED->value,
            $this->getResponseData()['status']
        );
    }

    #[TestDox('Connection response: only requested user can respond')]
    public function testOnlyRequestedUserCanRespond(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();
        $connection = new Connection($userA, $userB);
        $this->conRepo->save($connection, true);

        $this->signIn($userA);
        $this->sendRequest($connection->getId(), ConnectionStatusEnum::ACCEPTED->value);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function sendRequest(int $id, string $status): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('se_connection_response', ['id' => $id]),
            parameters: ['status' => $status],
        );
    }
}
