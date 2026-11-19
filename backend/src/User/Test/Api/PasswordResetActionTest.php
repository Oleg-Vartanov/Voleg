<?php

namespace App\User\Test\Api;

use App\Core\Service\TokenHasher;
use App\Core\Test\ApiTestCase;
use App\User\Model\Entity\PasswordResetToken;
use App\User\Repository\PasswordResetTokenRepository;
use App\User\Service\AuthService;
use App\User\Service\PasswordResetService;
use App\User\Service\UserService;
use App\User\Test\Trait\EmailedTokenTestTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class PasswordResetActionTest extends ApiTestCase
{
    use EmailedTokenTestTrait;

    private PasswordResetService $passwordResetService;

    public function setUp(): void
    {
        parent::setUp();
        $this->passwordResetService = static::getContainer()->get(PasswordResetService::class);
    }

    #[TestDox('Password reset: success removes the token')]
    public function testSuccess(): void
    {
        $user = $this->createUser();
        $this->passwordResetService->requestReset($user);

        $this->sendRequest([
            'token' => self::lastEmailedToken(),
            'password' => self::DEFAULT_PASSWORD . 'new',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertTrue(
            $this->getService(UserService::class)->isPasswordValid($user, self::DEFAULT_PASSWORD . 'new')
        );
        self::assertNull($this->getService(PasswordResetTokenRepository::class)->findByUser($user));
    }

    #[TestDox('Password reset: unknown token')]
    public function testUnknownToken(): void
    {
        $this->sendRequest([
            'token' => str_repeat('a', 64),
            'password' => self::DEFAULT_PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Password reset: expired token')]
    public function testExpiredToken(): void
    {
        $user = $this->createUser();
        $secret = $this->getService(TokenHasher::class)->generate();
        $this->getService(PasswordResetTokenRepository::class)->save(
            new PasswordResetToken($user, $secret->hash, new DateTimeImmutable('-1 second')),
            true,
        );

        $this->sendRequest([
            'token' => $secret->plain,
            'password' => self::DEFAULT_PASSWORD . 'new',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Password reset: a verification token is not accepted')]
    public function testOtherTokenTypeRejected(): void
    {
        $user = $this->createUser(verified: false);
        $this->getService(AuthService::class)->sendVerificationEmail($user);

        $this->sendRequest([
            'token' => self::lastEmailedToken(),
            'password' => self::DEFAULT_PASSWORD . 'new',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertTrue($this->getService(UserService::class)->isPasswordValid($user, self::DEFAULT_PASSWORD));
    }

    #[TestDox('Password reset: missing token')]
    public function testMissingToken(): void
    {
        $this->sendRequest(['password' => self::DEFAULT_PASSWORD]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Password reset: rate limit')]
    public function testRateLimit(): void
    {
        foreach (range(1, 4) as $i) {
            $this->sendRequest([
                'token' => str_repeat('b', 64),
                'password' => self::DEFAULT_PASSWORD,
            ]);
            if ($i === 4) {
                self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
            } else {
                self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
            }
        }
    }

    private function sendRequest(array $params = []): Response
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('password_reset'),
            parameters: $params
        );

        return $this->client->getResponse();
    }
}
