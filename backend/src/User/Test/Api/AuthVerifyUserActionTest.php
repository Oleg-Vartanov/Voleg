<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthVerifyUserActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Verify user success')]
    public function testVerifyUserSuccess(): void
    {
        $user = $this->createUser(verified: false);

        $response = $this->sendRequest([
            'userId' => $user->getId(),
            'code' => $user->getVerificationCode(),
            'redirectUrl' => 'https://example.com',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            '<!-- TEMPLATE: verify_success -->',
            $response->getContent()
        );
        $this->assertSelectorTextContains('#display-name', $user->getDisplayName());
        $this->assertSelectorTextContains('#support-email', 'support@mail.com');
        $this->assertSelectorExists('a[href="https://example.com"]');
    }

    #[TestDox('Verify user fail')]
    public function testVerifyUserFail(): void
    {
        $user = $this->createUser(verified: false);

        $response = $this->sendRequest([
            'userId' => $user->getId(),
            'code' => 'wrong-code',
            'redirectUrl' => 'https://example.com',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            '<!-- TEMPLATE: verify_fail -->',
            $response->getContent()
        );
        $this->assertSelectorTextContains('#display-name', $user->getDisplayName());
        $this->assertSelectorTextContains('#support-email', 'support@mail.com');
    }

    #[TestDox('Verify user not found')]
    public function testVerifyUserNotFound(): void
    {
        $this->sendRequest([
            'userId' => 0,
            'code' => 'code',
            'redirectUrl' => 'https://example.com',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify user invalid link')]
    public function testVerifyUserInvalidLink(): void
    {
        $this->sendRequest([
            'userId' => 11,
            'code' => 69,
            'redirectUrl' => 69,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function sendRequest(array $parameters): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('sign_up_verify'),
            parameters: $parameters,
            server: ['CONTENT_TYPE' => 'application/json']
        );

        return $this->client->getResponse();
    }
}
