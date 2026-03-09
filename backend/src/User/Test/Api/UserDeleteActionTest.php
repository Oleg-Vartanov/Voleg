<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserDeleteActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('User DELETE: success')]
    public function testUserDeleteSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertNull($deletedUser, 'User should be deleted from database.');
    }

    #[TestDox('User DELETE: access denied')]
    public function testUserDeleteAccessDenied(): void
    {
        $user = $this->createUser();
        $userDeleted = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($userDeleted->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('User DELETE: not found')]
    public function testUserDeleteNotFound(): void
    {
        $user = $this->createUser(['roles' => [RoleEnum::ROLE_ADMIN->value]]);
        $this->signIn($user);
        $this->sendRequest(0);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
