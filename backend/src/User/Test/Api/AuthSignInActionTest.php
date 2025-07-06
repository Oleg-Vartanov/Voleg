<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Controller\AuthSignInAction;
use App\User\Test\Trait\UserTestTrait;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthSignInActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Sign in action: success')]
    public function testSignInSuccess(): void
    {
        $user = $this->createUser();

        $response = $this->sendRequest([
            'email' => $user->getEmail(),
            'password' => self::DEFAULT_PASSWORD,
        ]);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($data['token']);
    }

    #[TestDox('Sign in action: invalid credentials')]
    public function testSignInInvalidCredentials(): void
    {
        $response = $this->sendRequest([
            'email' => 'fail',
            'password' => 'fail',
        ]);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[TestDox('Sign in action: unverified')]
    public function testSignInUnverified(): void
    {
        $user = $this->createUser(verified: false);

        $response = $this->sendRequest([
            'email' => $user->getEmail(),
            'password' => self::DEFAULT_PASSWORD,
        ]);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[TestDox('Sign in action: logic exception')]
    public function testSignInLogicException(): void
    {
        $controller = new AuthSignInAction();
        $this->expectException(LogicException::class);
        $controller->__invoke();
    }

    private function sendRequest(array $content): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_in'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content)
        );

        return $this->client->getResponse();
    }
}
