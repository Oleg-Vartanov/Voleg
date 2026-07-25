<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

#[TestDox('User')]
class UserGetListActionTest extends ApiTestCase
{
    #[TestDox('User GET list: success')]
    public function testUserGetListSuccess(): void
    {
        $user = $this->createUser();

        $this->sendRequest(
            $user->getUsername()
        );

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getUsername(), $this->getResponseData()[0]['username']);
    }

    #[TestDox('User GET list: partial username match')]
    public function testUserGetListPartialUsernameMatch(): void
    {
        $user = $this->createUser();

        $this->sendRequest(substr($user->getUsername(), 0, 4));

        self::assertResponseIsSuccessful();
        $usernames = array_column($this->getResponseData(), 'username');
        self::assertContains($user->getUsername(), $usernames);
    }

    private function sendRequest(?string $username): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('user_get_list'),
            parameters: [
                'username' => $username,
                'offset' => 0,
                'limit' => 100,
            ]
        );
    }
}
