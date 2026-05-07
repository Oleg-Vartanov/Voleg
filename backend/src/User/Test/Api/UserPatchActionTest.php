<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Repository\UserTokenRepository;
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
        $patchedUser = $this->userRepository->findById($user->getId());

        /** @var UserTokenRepository $tokenRepository */
        $tokenRepository = static::getContainer()->get(UserTokenRepository::class);
        $emailChangeToken = $tokenRepository->findOneByUser($user, UserTokenTypeEnum::EMAIL_CHANGE);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($user->getEmail(), $patchedUser->getEmail());
        self::assertSame('patched-email@mail.com', $emailChangeToken->getEmailChange());
        self::assertSame('patched-name', $patchedUser->getDisplayName());
        self::assertSame('patched-tag', $patchedUser->getTag());
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
        $user = $this->createUser(['roles' => [RoleEnum::ROLE_ADMIN->value]]);
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
