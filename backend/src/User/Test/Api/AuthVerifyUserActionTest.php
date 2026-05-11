<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\Core\ValueObject\Secret;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Repository\UserTokenRepository;
use App\User\Service\UserTokenService;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthVerifyUserActionTest extends ApiTestCase
{
    #[TestDox('Verify user: success')]
    public function testSuccess(): void
    {
        $user = $this->createUser(verified: false);
        [$token, $secret] = $this->createValidationToken($user);

        $this->sendRequest([
            'selector' => $token->getSelector(),
            'secret' => $secret->plain,
        ]);

        self::assertTrue($user->isVerified());
        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.auth-verification-success'),
        );
    }

    #[TestDox('Verify user: wrong secret')]
    public function testWrongSecret(): void
    {
        $user = $this->createUser(verified: false);
        [$token, $secret] = $this->createValidationToken($user);

        $this->sendRequest([
            'selector' => $token->getSelector(),
            'secret' => 'wrong-secret',
        ]);

        self::assertFalse($user->isVerified());
        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.auth-verification-fail'),
        );
    }

    #[TestDox('Verify user: wrong selector')]
    public function testWrongSelector(): void
    {
        $this->sendRequest(['selector' => 'verify-selector', 'secret' => 'test']);

        self::assertResponseStatusCodeSame(Response::HTTP_SEE_OTHER);
        self::assertResponseHeaderSame(
            'Location',
            $this->getParameter('client.url.auth-verification-fail'),
        );
    }

    #[TestDox('Verify user: invalid link')]
    public function testVerifyUserInvalidLink(): void
    {
        $this->sendRequest(['userId' => -1, 'code' => []]);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Verify user: rate limit')]
    public function testRateLimit(): void
    {
        foreach (range(1, 4) as $i) {
            $this->sendRequest(['selector' => 'verifyUserRateLimit', 'secret' => 'test']);
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

    /**
     * @return array{UserToken, Secret}
     */
    private function createValidationToken(User $user): array
    {
        /**
         * @var UserTokenService $tokenService
         * @var UserToken $tokenRepository
         */
        $tokenService = self::getContainer()->get(UserTokenService::class);
        $tokenRepository = self::getContainer()->get(UserTokenRepository::class);

        [$token, $secret] = $tokenService->createToken(
            type: UserTokenTypeEnum::VERIFICATION,
            user: $user,
        );
        $tokenRepository->save($token, true);

        return [$token, $secret];
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
