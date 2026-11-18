<?php

namespace App\User\Test\Api;

use App\Core\Service\TokenHasher;
use App\Core\Test\ApiTestCase;
use App\User\Repository\PasswordResetTokenRepository;
use App\User\Test\Trait\EmailedTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class PasswordForgotActionTest extends ApiTestCase
{
    use EmailedTokenTestTrait;

    #[TestDox('Password forgot: success request')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->sendRequest(['email' => $user->getEmail()]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $hash = $this->getService(TokenHasher::class)->hash(self::lastEmailedToken());
        self::assertNotNull($this->getService(PasswordResetTokenRepository::class)->findByTokenHash($hash));
    }

    #[TestDox('Password forgot: user not found')]
    public function testUserNotFound(): void
    {
        $this->sendRequest(['email' => 'no-user-email@test.com']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[TestDox('Password forgot: invalid email')]
    public function testInvalidEmail(): void
    {
        $this->sendRequest(['email' => 'invalid-email']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function sendRequest(array $params = []): Response
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('password_forgot'),
            parameters: $params
        );

        return $this->client->getResponse();
    }
}
