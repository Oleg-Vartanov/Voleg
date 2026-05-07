<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\DataFixture\UserFixture;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Service\PasswordResetService;
use App\User\Test\Trait\UserTestTrait;
use App\User\Test\Trait\UserTokenTestTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class PasswordResetActionTest extends ApiTestCase
{
    use UserTestTrait, UserTokenTestTrait;

    private PasswordResetService $passwordResetService;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenSetUp();
        $this->bootUserTest();
        $this->passwordResetService = static::getContainer()->get(
            PasswordResetService::class
        );
    }

    #[TestDox('Password reset: success request')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->mockToken('selector'.$user->getId(), 'secret'.$user->getId());
        $this->passwordResetService->requestReset($user);

        $this->sendRequest([
            'selector' => 'selector'.$user->getId(),
            'secret' => 'secret'.$user->getId(),
            'password' => UserFixture::DEFAULT_PASSWORD.'new',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertTrue(
            $this->userService->isPasswordValid(
                $user,
                UserFixture::DEFAULT_PASSWORD.'new'
            )
        );

        $token = $this->userTokenRepository->findOneByUser($user, UserTokenTypeEnum::PASSWORD_RESET);
        self::assertNull($token);
    }

    #[TestDox('Password reset: wrong secret')]
    public function testWrongSecret(): void
    {
        $user = $this->createUser();
        $this->mockToken('selector'.$user->getId(), 'secret'.$user->getId());
        $this->passwordResetService->requestReset($user);

        $this->sendRequest([
            'selector' => 'selector'.$user->getId(),
            'secret' => 'wrong-secret',
            'password' => UserFixture::DEFAULT_PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Password reset: expired token')]
    public function testExpiredToken(): void
    {
        $user = $this->createUser();
        $this->mockToken(
            'selector'.$user->getId(),
            'secret'.$user->getId(),
            new DateTimeImmutable('-1 day'),
        );
        $this->passwordResetService->requestReset($user);

        $this->sendRequest([
            'selector' => 'selector'.$user->getId(),
            'secret' => 'secret'.$user->getId(),
            'password' => UserFixture::DEFAULT_PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Password reset: no token')]
    public function testNoToken(): void
    {
        $this->sendRequest([
            'selector' => 'non-existent-selector',
            'secret' => 'test',
            'password' => UserFixture::DEFAULT_PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Password reset: rate limit')]
    public function testRateLimit(): void
    {
        foreach (range(1, 6) as $i) {
            $this->sendRequest([
                'selector' => 'passwordResetRateLimitTest',
                'secret' => 'test',
                'password' => UserFixture::DEFAULT_PASSWORD,
            ]);
            if ($i === 6) {
                self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
            } else {
                self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
            }
        }
    }

    private function sendRequest(array $content = []): Response
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('password_reset'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($content),
        );

        return $this->client->getResponse();
    }
}