<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Service\EmailChangeService;
use App\User\Test\Trait\UserTestTrait;
use App\User\Test\Trait\UserTokenTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[TestDox('Auth')]
class AuthVerifyEmailChangeActionTest extends ApiTestCase
{
    use UserTestTrait, UserTokenTestTrait;

    private EmailChangeService $emailChangeService;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->tokenSetUp();
        $this->bootUserTest();
        $this->emailChangeService = static::getContainer()->get(EmailChangeService::class);
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    #[TestDox('Verify email change: success')]
    public function testVerifyEmailChangeSuccess(): void
    {
        $user = $this->createUser();
        $this->mockToken('selector'.$user->getId(), 'secret'.$user->getId());
        $this->emailChangeService->requestEmailChange($user, 'new-email@mail.com');

        $this->sendRequest([
            'selector' => 'selector'.$user->getId(),
            'secret' => 'secret'.$user->getId(),
        ]);

        self::assertSame('new-email@mail.com', $user->getEmail());
        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.email-change-success'),
        );

        $token = $this->userTokenRepository->findOneByUser($user, UserTokenTypeEnum::EMAIL_CHANGE);
        self::assertNull($token);
    }

    #[TestDox('Verify email change: wrong secret')]
    public function testWrongSecret(): void
    {
        $user = $this->createUser();
        $oldEmail = $user->getEmail();
        $this->mockToken('selector'.$user->getId());
        $this->emailChangeService->requestEmailChange($user, 'new-email@mail.com');

        $this->sendRequest(['selector' => 'selector'.$user->getId(), 'secret' => 'wrong-secret']);

        self::assertSame($oldEmail, $user->getEmail());
        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.email-change-fail'),
        );
    }

    #[TestDox('Verify email change: wrong selector')]
    public function testWrongSelector(): void
    {
        $this->sendRequest(['selector' => 'wrong-selector', 'secret' => 'test-secret']);

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.email-change-fail'),
        );
    }

    #[TestDox('Verify email change: invalid link')]
    public function testInvalidLink(): void
    {
        $this->sendRequest(['selector' => [], 'secret' => []]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify email change: rate limit')]
    public function testRateLimit(): void
    {
        $user = $this->createUser();
        $this->mockToken();
        $this->emailChangeService->requestEmailChange($user, 'new-email@mail.com');

        foreach (range(1, 6) as $i) {
            $this->sendRequest(['selector' => 'test', 'secret' => 'wrong-secret']);
            if ($i === 6) {
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
