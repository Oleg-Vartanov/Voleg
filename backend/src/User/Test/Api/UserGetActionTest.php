<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Enum\RoleEnum;
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

        $response = $this->sendRequest($user->getId());
        $responseUser = json_decode($response->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getTag(), $responseUser['tag']);
    }

    #[TestDox('User GET: success admin')]
    public function testUserGetSuccessAdmin(): void
    {
        $user = $this->createUser(isAdmin: true);
        $this->signIn($user);

        $response = $this->sendRequest($user->getId());
        $responseUser = json_decode($response->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getEmail(), $responseUser['email']);
    }

    #[TestDox('User GET: not found')]
    public function testUserGetNotFound(): void
    {
        $response = $this->sendRequest(0);

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function sendRequest(int $id): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('user_get', [
                'id' => $id,
            ]),
        );

        return $this->client->getResponse();
    }
}
