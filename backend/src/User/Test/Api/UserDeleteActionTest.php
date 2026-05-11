<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Enum\RoleEnum;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserDeleteActionTest extends ApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    #[TestDox('User DELETE: success')]
    public function testUserDeleteSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($user->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedUser = $this->getService(UserRepository::class)->findById($user->getId());
        self::assertNull($deletedUser, 'User should be deleted from database.');
    }

    #[TestDox('User DELETE: access denied')]
    public function testUserDeleteAccessDenied(): void
    {
        $user = $this->createUser();
        $userDeleted = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($userDeleted->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('User DELETE: not found')]
    public function testUserDeleteNotFound(): void
    {
        $user = $this->createUser(isAdmin: true);
        $this->signIn($user);
        $this->sendRequest(0);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function sendRequest(int $id): Response
    {
        $this->client->request(
            method: Request::METHOD_DELETE,
            uri: $this->router->generate('user_delete', [
                'id' => $id,
            ]),
        );

        return $this->client->getResponse();
    }
}
