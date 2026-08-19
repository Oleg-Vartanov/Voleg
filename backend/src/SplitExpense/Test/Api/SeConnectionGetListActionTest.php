<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionGetListActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    #[TestDox('Connection GET list: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser(flush: false);
        $this->createConnection($user, $this->createUser(flush: false), flush: false);
        $this->createConnection($user, $this->createUser());

        $this->signIn($user);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(2, $this->getResponseData());
    }

    #[TestDox('Connection GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('Connection GET list: filter by partner username')]
    public function testFilterByUsername(): void
    {
        $user = $this->createUser(['username' => 'alice'], flush: false);
        $this->createConnection(
            $user,
            $this->createUser(['username' => 'partner-user']),
        );

        $this->signIn($user);
        $this->sendRequest(['username' => 'user', 'usersOnly' => true, 'status' => 'accepted']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $usernames = array_column($this->getResponseData(), 'username');
        foreach ($usernames as $username) {
            self::assertStringContainsStringIgnoringCase('user', $username);
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function sendRequest(array $parameters = []): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_connection_get_list'),
            parameters: $parameters,
        );
    }
}
