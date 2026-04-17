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

    #[TestDox('Verify user: success')]
    public function testVerifyUserSuccess(): void
    {
        $user = $this->createUser(verified: false);

        $this->sendRequest([
            'userId' => $user->getId(),
            'code' => $user->getVerificationCode(),
        ]);

        $this->assertTrue($user->isVerified());
        $this->assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        $this->assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.auth-verification-success'),
        );
    }

    #[TestDox('Verify user: fail')]
    public function testVerifyUserFail(): void
    {
        $user = $this->createUser(verified: false);

        $this->sendRequest(['userId' => $user->getId(), 'code' => 'wrong-code']);

        $this->assertFalse($user->isVerified());
        $this->assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        $this->assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.auth-verification-fail'),
        );
    }

    #[TestDox('Verify user: not found')]
    public function testVerifyUserNotFound(): void
    {
        $this->sendRequest(['userId' => 0, 'code' => 'code']);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify user: invalid link')]
    public function testVerifyUserInvalidLink(): void
    {
        $this->sendRequest(['userId' => -1, 'code' => []]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getParameter(string $name): string
    {
        return self::getContainer()->get('parameter_bag')->get($name);
    }

    private function sendRequest(array $parameters): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('sign_up_verify'),
            parameters: $parameters,
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        return $this->client->getResponse();
    }
}
