<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[TestDox('User')]
class PasswordChangeActionTest extends ApiTestCase
{
    use UserTestTrait;

    private UserPasswordHasherInterface $passwordHasher;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    #[TestDox('User change password: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser(['password' => UserFixture::DEFAULT_PASSWORD]);
        $this->signIn($user);
        $this->sendRequest([
            'currentPassword' => UserFixture::DEFAULT_PASSWORD,
            'newPassword' => '!NewPassword1',
        ]);
        /** @var User $patchedUser */
        $updatedUser = $this->userRepository->findById($user->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertTrue(
            $this->passwordHasher->isPasswordValid($updatedUser, '!NewPassword1')
        );
    }

    #[TestDox('User change password: wrong password')]
    public function testWrongPassword(): void
    {
        $user = $this->createUser(['password' => UserFixture::DEFAULT_PASSWORD]);
        $this->signIn($user);
        $this->sendRequest([
            'currentPassword' => 'wrong-password',
            'newPassword' => '!NewPassword1',
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('User change password: same password')]
    public function testSamePassword(): void
    {
        $user = $this->createUser(['password' => UserFixture::DEFAULT_PASSWORD]);
        $this->signIn($user);
        $this->sendRequest([
            'currentPassword' => UserFixture::DEFAULT_PASSWORD,
            'newPassword' => UserFixture::DEFAULT_PASSWORD,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('User change password: invalid password')]
    public function testInvalidPassword(): void
    {
        $user = $this->createUser(['password' => UserFixture::DEFAULT_PASSWORD]);
        $this->signIn($user);
        $this->sendRequest([
            'currentPassword' => UserFixture::DEFAULT_PASSWORD,
            'newPassword' => 'invalid-password',
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('User change password: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('User change password: rate limit')]
    public function testRateLimit(): void
    {
        $user = $this->createUser(['password' => UserFixture::DEFAULT_PASSWORD]);
        $this->signIn($user);
        foreach (range(1, 6) as $i) {
            $this->sendRequest(['currentPassword' => 'wrong-password', 'newPassword' => '!NewPassword1',]);
            if ($i === 6) {
                self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
            } else {
                self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    private function sendRequest(array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('password_change'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}
