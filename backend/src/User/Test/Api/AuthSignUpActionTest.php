<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthSignUpActionTest extends ApiTestCase
{
    use UserTokenTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenSetUp();
    }

    #[TestDox('Sign up action: success')]
    public function testSuccess(): void
    {
        $testUser = [
            'email' => 'user-auth-test@example.com',
            'password' => '!Qwerty1',
            'username' => 'user-auth-test',
            'code' => 'sign-up-code',
        ];

        $this->mockToken('selector-auth-test', 'secret-auth-test');
        $this->signUpRequest($testUser);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);;
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'selector-auth-test');
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'secret-auth-test');
    }

    #[TestDox('Sign up action: validation error')]
    public function testValidationError(): void
    {
        $this->signUpRequest([
            'email' => 'john.doe',
            'password' => 'qwert y',
            'username' => '',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);;
    }

    private function signUpRequest(array $params): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_up'),
            parameters: $params
        );
    }
}
