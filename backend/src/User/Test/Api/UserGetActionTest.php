<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserGetActionTest extends ApiTestCase
{
    #[TestDox('User GET: success')]
    public function testUserGetSuccess(): void
    {
        $user = $this->createUser();

        $this->sendRequest($user->getId());

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getUsername(), $this->getResponseData()['username']);
    }

    #[TestDox('User GET: success admin')]
    public function testUserGetSuccessAdmin(): void
    {
        $user = $this->createUser(isAdmin: true);
        $this->signIn($user);

        $this->sendRequest($user->getId());

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getEmail(), $this->getResponseData()['email']);
    }

    #[TestDox('User GET: not found')]
    public function testUserGetNotFound(): void
    {
        $this->sendRequest(0);

        self::assertEquals(Response::HTTP_NOT_FOUND, $this->getResponseStatusCode());
    }

    private function sendRequest(int $id): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('user_get', [
                'id' => $id,
            ]),
        );
    }
}
