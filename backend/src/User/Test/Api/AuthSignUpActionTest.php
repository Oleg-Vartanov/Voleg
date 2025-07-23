<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthSignUpActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Sign up action: success')]
    public function testSignUpActionSuccess(): void
    {
        $lastUserId = $this->userRepository->findOneBy([], ['id' => 'desc'])?->getId() ?? 0;

        $testUser = [
            'email' => 'user' . ($lastUserId + 1) . '@example.com',
            'password' => '!Qwerty1',
            'displayName' => 'John Doe',
            'tag' => 'user' . ($lastUserId + 1),
            'verificationEmailRedirectUrl' => 'https://example.com',
            'code' => 'sign-up-code',
        ];

        $response = $this->signUpRequest($testUser);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmailHtmlBodyContains($this->getMailerMessage(), $testUser['displayName']);
    }

    #[TestDox('Sign up action: validation error')]
    public function testSignUpActionValidationError(): void
    {
        $response = $this->signUpRequest([
            'email' => 'john.doe',
            'password' => 'qwert y',
            'displayName' => '',
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    private function signUpRequest(array $array): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_up'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($array)
        );

        return $this->client->getResponse();
    }
}
