<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Repository\UserRepository;
use App\User\Test\Trait\UserTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthSignUpActionTest extends ApiTestCase
{
    use UserTokenTestTrait;

    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenSetUp();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    #[TestDox('Sign up action: success')]
    public function testSuccess(): void
    {
        $id = ($this->userRepository->lastId() ?? 0) + 1;

        $testUser = [
            'email' => 'user' . $id . '@example.com',
            'password' => '!Qwerty1',
            'displayName' => 'John Doe',
            'tag' => 'user' . $id,
            'code' => 'sign-up-code',
        ];

        $this->mockToken('selector'.$id, 'secret'.$id);
        $response = $this->signUpRequest($testUser);

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'selector'.$id);
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'secret'.$id);
    }

    #[TestDox('Sign up action: validation error')]
    public function testValidationError(): void
    {
        $response = $this->signUpRequest([
            'email' => 'john.doe',
            'password' => 'qwert y',
            'displayName' => '',
        ]);

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
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
