<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\DataFixture\UserFixture;
use App\User\Http\V1\AuthSignInAction;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthSignInActionTest extends ApiTestCase
{
    #[TestDox('Sign in action: success')]
    public function testSignInSuccess(): void
    {
        $user = $this->createUser();

        $response = $this->sendRequest([
            'email' => $user->getEmail(),
            'password' => UserFixture::DEFAULT_PASSWORD,
        ]);

        $data = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($data['token']);
    }

    #[TestDox('Sign in action: invalid credentials')]
    public function testSignInInvalidCredentials(): void
    {
        $this->sendRequest(['email' => 'fail', 'password' => 'fail']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('Sign in action: unverified')]
    public function testSignInUnverified(): void
    {
        $user = $this->createUser(verified: false);
        $this->sendRequest([
            'email' => $user->getEmail(),
            'password' => UserFixture::DEFAULT_PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('Sign in action: logic exception')]
    public function testSignInLogicException(): void
    {
        $controller = new AuthSignInAction();
        self::expectException(LogicException::class);
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
