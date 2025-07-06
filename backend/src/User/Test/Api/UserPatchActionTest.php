<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\User;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserPatchActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('User PATCH: success')]
    public function testUserPatchSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($user->getId(), [
            'email' => 'patched-email@mail.com',
            'displayName' => 'patched-name',
            'tag' => 'patched-tag',
        ]);
        /** @var User $patchedUser */
        $patchedUser = $this->entityManager->getRepository(User::class)->find($user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame($user->getId(), $patchedUser->getId());
        $this->assertSame('patched-email@mail.com', $patchedUser->getEmail());
        $this->assertSame('patched-name', $patchedUser->getDisplayName());
        $this->assertSame('patched-tag', $patchedUser->getTag());
    }

    #[TestDox('User PATCH: access denied')]
    public function testUserPatchAccessDenied(): void
    {
        $user = $this->createUser();
        $userPatched = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($userPatched->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('User PATCH: not found')]
    public function testUserPatchNotFound(): void
    {
        $user = $this->createUser(['roles' => ['ROLE_ADMIN']]);
        $this->signIn($user);
        $this->sendRequest(0);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function sendRequest(int $id, array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_PATCH,
            uri: $this->router->generate('user_patch', [
                'id' => $id,
            ]),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}
