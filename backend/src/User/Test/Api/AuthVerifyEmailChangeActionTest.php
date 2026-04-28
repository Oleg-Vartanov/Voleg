<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthVerifyEmailChangeActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('Verify email change: success')]
    public function testVerifyEmailChangeSuccess(): void
    {
        $user = $this->createUser();
        $this->userService->requestEmailChange($user, 'new-email@mail.com');
        $code = $user->getEmailChangeCode();

        $this->sendRequest([
            'userId' => $user->getId(),
            'code' => $code,
        ]);

        self::assertSame('new-email@mail.com', $user->getEmail());
        self::assertNull($user->getEmailChange());

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.email-change-success'),
        );
    }

    #[TestDox('Verify email change: fail')]
    public function testVerifyEmailChangeFail(): void
    {
        $user = $this->createUser();
        $oldEmail = $user->getEmail();
        $this->userService->requestEmailChange($user, 'new-email@mail.com');

        $this->sendRequest(['userId' => $user->getId(), 'code' => 'wrong-code']);

        self::assertSame($oldEmail, $user->getEmail());
        self::assertSame('new-email@mail.com', $user->getEmailChange());

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.email-change-fail'),
        );
    }

    #[TestDox('Verify email change: invalid link')]
    public function testVerifyEmailChangeInvalidLink(): void
    {
        $this->sendRequest(['userId' => -1, 'code' => []]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getParameter(string $name): string
    {
        return self::getContainer()->get('parameter_bag')->get($name);
    }

    private function sendRequest(array $parameters): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('email_change_verify'),
            parameters: $parameters,
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        return $this->client->getResponse();
    }
}
