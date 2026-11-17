<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Auth')]
class AuthRefreshTokenTest extends ApiTestCase
{
    #[TestDox('Sign in sets a refresh cookie')]
    public function testSignInSetsRefreshCookie(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        self::assertNotNull($this->refreshCookie());
    }

    #[TestDox('Refresh rotates the cookie and returns an access token')]
    public function testRefreshRotates(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $previous = $this->refreshCookie()?->getValue();

        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($this->getResponseData()['token']);
        self::assertNotSame($previous, $this->refreshCookie()?->getValue());
    }

    #[TestDox('Reusing a rotated refresh cookie revokes the session')]
    public function testReuseRevokesSession(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $stolen = $this->refreshCookie()?->getValue();
        self::assertNotNull($stolen);

        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $current = $this->refreshCookie()?->getValue();
        self::assertNotNull($current);

        $this->replaceRefreshCookie($stolen);
        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->replaceRefreshCookie($current);
        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('Sign out deletes the refresh cookie')]
    public function testSignOut(): void
    {
        $user = $this->createUser();
        $this->signIn($user);

        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_sign_out'));
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[TestDox('Password change revokes older refresh cookies')]
    public function testPasswordChangeRevokesRefreshTokens(): void
    {
        $user = $this->createUser();
        $this->signIn($user);
        $previous = $this->refreshCookie()?->getValue();
        self::assertNotNull($previous);

        $this->client->jsonRequest(
            Request::METHOD_POST,
            $this->router->generate('password_change'),
            [
                'currentPassword' => self::DEFAULT_PASSWORD,
                'newPassword' => '!NewPassword1',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNotEmpty($this->getResponseData()['token']);
        $current = $this->refreshCookie()?->getValue();
        self::assertNotNull($current);
        self::assertNotSame($previous, $current);

        $this->replaceRefreshCookie($previous);
        $this->client->jsonRequest(Request::METHOD_POST, $this->router->generate('auth_refresh'));
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function refreshCookie(): ?Cookie
    {
        return $this->client->getCookieJar()->get($this->cookieName(), $this->cookiePath());
    }

    private function replaceRefreshCookie(string $value): void
    {
        $this->client->getCookieJar()->expire($this->cookieName(), $this->cookiePath());
        $this->client->getCookieJar()->set(new Cookie(
            $this->cookieName(),
            $value,
            null,
            $this->cookiePath(),
            'localhost',
        ));
    }

    private function cookieName(): string
    {
        return self::getContainer()->getParameter('refresh_token.cookie');
    }

    private function cookiePath(): string
    {
        return self::getContainer()->getParameter('refresh_token.path');
    }
}
