<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionDeleteActionTest extends ApiTestCase
{
    private UserRepository $userRepo;
    private SeConnectionRepository $conRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->getService(UserRepository::class);
        $this->conRepo = $this->getService(SeConnectionRepository::class);
    }

    #[TestDox('Connection DELETE: success')]
    public function testSuccess(): void
    {
        $userA = $this->userRepo->findByTag('user1');
        $userB = $this->userRepo->findByTag('user2');
        $connection = $this->conRepo->findOneByUsers($userA, $userB);
        self::assertNotNull($connection);

        $this->signIn($userA);
        $this->sendRequest($connection->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull($this->conRepo->findOneByUsers($userA, $userB));
    }

    #[TestDox('Connection DELETE: access denied')]
    public function testAccessDenied(): void
    {
        $connection = $this->conRepo->findOneByUsers(
            $this->userRepo->findByTag('user1'),
            $this->userRepo->findByTag('user3'),
        );
        self::assertNotNull($connection);

        $this->signIn($this->userRepo->findByTag('user2'));

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
