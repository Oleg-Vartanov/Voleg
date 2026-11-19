<?php

namespace App\User\Test\Api;

use App\Core\Service\TokenHasher;
use App\Core\Test\ApiTestCase;
use App\User\Repository\VerificationTokenRepository;
use App\User\Test\Trait\EmailedTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class SignUpActionTest extends ApiTestCase
{
    use EmailedTokenTestTrait;

    #[TestDox('Sign up action: success')]
    public function testSuccess(): void
    {
        $index = bin2hex(random_bytes(6));
        $this->signUpRequest([
            'email' => 'user-auth-test-' . $index . '@example.com',
            'password' => '!Qwerty1',
            'username' => 'user-auth-test-' . $index,
            'code' => 'sign-up-code',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $hash = $this->getService(TokenHasher::class)->hash(self::lastEmailedToken());
        self::assertNotNull($this->getService(VerificationTokenRepository::class)->findByTokenHash($hash));
    }

    #[TestDox('Sign up action: validation error')]
    public function testValidationError(): void
    {
        $this->signUpRequest([
            'email' => 'john.doe',
            'password' => 'qwert y',
            'username' => '',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
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
