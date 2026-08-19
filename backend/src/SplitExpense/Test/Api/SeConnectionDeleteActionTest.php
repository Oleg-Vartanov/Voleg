<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionDeleteActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private SeConnectionRepository $conRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->conRepo = $this->getService(SeConnectionRepository::class);
    }

    #[TestDox('Connection DELETE: success')]
    public function testSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser(flush: false);
        $connection = $this->createConnection($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($connection->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull($this->conRepo->findOneByUsers($userA, $userB));
    }

    #[TestDox('Connection DELETE: access denied')]
    public function testAccessDenied(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser(flush: false);
        $userC = $this->createUser(flush: false);
        $connection = $this->createConnection($userA, $userB, SeConnectionStatusEnum::PENDING, false);
        $this->conRepo->flush();

        $this->signIn($userC);
        $this->sendRequest($connection->getId());
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Connection DELETE: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(0);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(int $id): void
    {
        $this->client->request(
            method: Request::METHOD_DELETE,
            uri: $this->router->generate('se_connection_delete', ['id' => $id]),
        );
    }
}
