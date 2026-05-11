<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class PasswordForgotActionTest extends ApiTestCase
{
    use UserTokenTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenSetUp();
    }

    #[TestDox('Password forgot: success request')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->mockToken('selector' . $user->getId(), 'secret' . $user->getId());
        $this->sendRequest(['email' => $user->getEmail()]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'selector' . $user->getId());
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'secret' . $user->getId());
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

    private function sendRequest(array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('password_forgot'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}
