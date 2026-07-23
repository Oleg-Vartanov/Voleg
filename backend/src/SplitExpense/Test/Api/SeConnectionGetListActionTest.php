<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionGetListActionTest extends ApiTestCase
{
    #[TestDox('Connection GET list: success')]
    public function testSuccess(): void
    {
        $user = $this->getService(UserRepository::class)->findByUsername('user1');
        $connections = $this->getService(SeConnectionRepository::class)->listForUser($user);

        $this->signIn($user);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(count($connections), $this->getResponseData());
    }

    #[TestDox('Connection GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_connection_get_list'),
        );
    }
}
