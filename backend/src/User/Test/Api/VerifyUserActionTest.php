<?php

namespace App\User\Test\Api;

use App\Core\Service\TokenHasher;
use App\Core\Test\ApiTestCase;
use App\User\Entity\VerificationToken;
use App\User\Repository\VerificationTokenRepository;
use App\User\Service\AuthService;
use App\User\Test\Trait\EmailedTokenTestTrait;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class VerifyUserActionTest extends ApiTestCase
{
    use EmailedTokenTestTrait;

    #[TestDox('Verify user: success removes the token')]
    public function testSuccess(): void
    {
        $user = $this->createUser(verified: false);
        $this->getService(AuthService::class)->sendVerificationEmail($user);

        $this->sendRequest(['token' => self::lastEmailedToken()]);

        self::assertResponseRedirects($this->getParameter('client.url.auth-verification-success'));
        self::assertTrue($user->isVerified());
        self::assertNull($this->getService(VerificationTokenRepository::class)->findByUser($user));
    }

    #[TestDox('Verify user: a link works only once')]
    public function testLinkWorksOnce(): void
    {
        $user = $this->createUser(verified: false);
        $this->getService(AuthService::class)->sendVerificationEmail($user);
        $token = self::lastEmailedToken();

        $this->sendRequest(['token' => $token]);
        $this->sendRequest(['token' => $token]);

        self::assertResponseRedirects($this->getParameter('client.url.auth-verification-fail'));
    }

    #[TestDox('Verify user: a new email replaces the earlier link')]
    public function testResendReplacesEarlierLink(): void
    {
        $user = $this->createUser(verified: false);
        $authService = $this->getService(AuthService::class);
        $authService->sendVerificationEmail($user);
        $earlier = self::lastEmailedToken();
        $authService->sendVerificationEmail($user);

        $this->sendRequest(['token' => $earlier]);

        self::assertResponseRedirects($this->getParameter('client.url.auth-verification-fail'));
        self::assertFalse($user->isVerified());
    }

    #[TestDox('Verify user: expired token')]
    public function testExpiredToken(): void
    {
        $user = $this->createUser(verified: false);
        $secret = $this->getService(TokenHasher::class)->generate();
        $this->getService(VerificationTokenRepository::class)->save(
            new VerificationToken($user, $secret->hash, new DateTimeImmutable('-1 second')),
            true,
        );

        $this->sendRequest(['token' => $secret->plain]);

        self::assertResponseRedirects($this->getParameter('client.url.auth-verification-fail'));
        self::assertFalse($user->isVerified());
    }

    #[TestDox('Verify user: unknown token')]
    public function testUnknownToken(): void
    {
        $this->sendRequest(['token' => str_repeat('a', 64)]);

        self::assertResponseRedirects($this->getParameter('client.url.auth-verification-fail'));
    }

    #[TestDox('Verify user: invalid link')]
    public function testInvalidLink(): void
    {
        $this->sendRequest(['userId' => -1, 'code' => []]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify user: rate limit')]
    public function testRateLimit(): void
    {
        foreach (range(1, 4) as $i) {
            $this->sendRequest(['token' => str_repeat('b', 64)]);
            if ($i === 4) {
                self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
            } else {
                self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
            }
        }
    }

    private function getParameter(string $name): string
    {
        return self::getContainer()->get(ParameterBagInterface::class)->get($name);
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
