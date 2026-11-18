<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\Core\Test\Trait\ContainerTestTrait;
use App\User\Repository\EmailChangeTokenRepository;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserPatchActionTest extends ApiTestCase
{
    use ContainerTestTrait;

    #[TestDox('User PATCH: success')]
    public function testUserPatchSuccess(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($user->getId(), [
            'email' => 'patched-email@mail.com',
            'username' => 'patched-username',
        ]);
        $patchedUser = $this->getService(UserRepository::class)->findById($user->getId());

        $emailChangeToken = $this->getService(EmailChangeTokenRepository::class)->findByUser($user);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($user->getEmail(), $patchedUser->getEmail());
        self::assertSame('patched-email@mail.com', $emailChangeToken?->getNewEmail());
        self::assertSame('patched-username', $patchedUser->getUsername());
    }

    #[TestDox('User PATCH: access denied')]
    public function testUserPatchAccessDenied(): void
    {
        $user = $this->createUser();
        $userPatched = $this->createUser();
        $this->signIn($user);
        $this->sendRequest($userPatched->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('User PATCH: not found')]
    public function testUserPatchNotFound(): void
    {
        $user = $this->createUser(isAdmin: true);
        $this->signIn($user);
        $this->sendRequest(0);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
