<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Repository\EmailChangeTokenRepository;
use App\User\Service\EmailChangeService;
use App\User\Test\Trait\EmailedTokenTestTrait;
use DateMalformedStringException;
use PHPUnit\Framework\Attributes\TestDox;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[TestDox('Auth')]
class VerifyEmailChangeActionTest extends ApiTestCase
{
    use EmailedTokenTestTrait;

    private EmailChangeService $emailChangeService;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->emailChangeService = static::getContainer()->get(EmailChangeService::class);
    }

    /**
     * @throws TransportExceptionInterface|RandomException|DateMalformedStringException
     */
    #[TestDox('Verify email change: success removes the token')]
    public function testVerifyEmailChangeSuccess(): void
    {
        $user = $this->createUser();
        $newEmail = 'new-' . bin2hex(random_bytes(6)) . '@mail.com';
        $this->emailChangeService->requestEmailChange($user, $newEmail);

        $this->sendRequest(['token' => self::lastEmailedToken()]);

        self::assertResponseRedirects($this->getParameter('client.url.email-change-success'));
        self::assertSame($newEmail, $user->getEmail());
        self::assertNull($this->getService(EmailChangeTokenRepository::class)->findByUser($user));
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    #[TestDox('Verify email change: fails when the new email was taken meanwhile')]
    public function testNewEmailTakenMeanwhile(): void
    {
        $user = $this->createUser();
        $oldEmail = $user->getEmail();
        $newEmail = 'taken-' . bin2hex(random_bytes(6)) . '@mail.com';
        $this->emailChangeService->requestEmailChange($user, $newEmail);
        $token = self::lastEmailedToken();
        $this->createUser(['email' => $newEmail]);

        $this->sendRequest(['token' => $token]);

        self::assertResponseRedirects($this->getParameter('client.url.email-change-fail'));
        self::assertSame($oldEmail, $user->getEmail());
    }

    #[TestDox('Verify email change: unknown token')]
    public function testUnknownToken(): void
    {
        $this->sendRequest(['token' => str_repeat('a', 64)]);

        self::assertResponseRedirects($this->getParameter('client.url.email-change-fail'));
    }

    #[TestDox('Verify email change: invalid link')]
    public function testInvalidLink(): void
    {
        $this->sendRequest(['token' => []]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify email change: rate limit')]
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
            uri: $this->router->generate('email_change_verify'),
            parameters: $parameters,
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        return $this->client->getResponse();
    }
}
