<?php

namespace App\User\Service;

use App\User\Entity\RefreshToken;
use App\User\Entity\User;
use App\User\Exception\RefreshTokenRejected;
use App\User\Repository\RefreshTokenRepository;
use App\User\ValueObject\IssuedRefreshToken;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The refresh token is a single 256-bit random value sent in an HttpOnly
 * cookie. Only its keyed hash (HMAC-SHA256) is stored, and every lookup hashes
 * the presented value first, so a leaked hash is not usable as a token. A fast
 * hash is safe here only because the token is long and random.
 */
class RefreshTokenService
{
    private const string TOKEN_FORMAT = '/^[a-f0-9]{64}$/';

    public function __construct(
        private readonly RefreshTokenRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(env: 'TOKEN_HASH_KEY')]
        private readonly string $hashKey,
        #[Autowire(param: 'refresh_token.cookie')]
        private readonly string $cookieName,
        #[Autowire(param: 'refresh_token.path')]
        private readonly string $cookiePath,
        #[Autowire(param: 'refresh_token.ttl')]
        private readonly int $ttl,
    ) {
        if ($ttl <= 0) {
            throw new InvalidArgumentException('REFRESH_TOKEN_TTL must be a positive number of seconds.');
        }
    }

    public function start(User $user, Response $response, Request $request): void
    {
        $issued = $this->issue($user);
        $this->repository->flush();
        $response->headers->setCookie($this->makeCookie($request, $issued->value, $issued->expiresAt));
    }

    /**
     * Exchanges the cookie for a new one. A key that was already rotated revokes
     * every refresh key for that account.
     */
    public function rotate(Request $request, Response $response): User
    {
        $hash = $this->hash($this->readToken($request));

        /** @var IssuedRefreshToken|null $issued null when the token is rejected */
        $issued = $this->entityManager->wrapInTransaction(function () use ($hash): ?IssuedRefreshToken {
            $token = $this->repository->findByTokenHash($hash);
            if ($token === null) {
                return null;
            }

            $user = $token->getUser();
            if ($token->isExpired() || $token->isUsed() || !$user->isVerified()) {
                if ($token->isExpired()) {
                    $this->repository->remove($token);
                } else {
                    $this->repository->removeAllForUser($user);
                }

                return null;
            }

            $token->markUsed();

            return $this->issue($user);
        });

        if ($issued === null) {
            throw new RefreshTokenRejected();
        }

        $response->headers->setCookie($this->makeCookie($request, $issued->value, $issued->expiresAt));

        return $issued->user;
    }

    public function revokeAll(User $user): void
    {
        $this->repository->removeAllForUser($user);
    }

    public function revokeCurrent(Request $request): void
    {
        try {
            $hash = $this->hash($this->readToken($request));
        } catch (RefreshTokenRejected) {
            return;
        }

        $token = $this->repository->findByTokenHash($hash);
        if ($token !== null) {
            $this->repository->remove($token, true);
        }
    }

    public function clearCookie(Request $request, Response $response): void
    {
        $response->headers->setCookie(
            $this->makeCookie($request, '', new DateTimeImmutable('@0'))
        );
    }

    /**
     * @throws RandomException|DateMalformedStringException
     */
    private function issue(User $user): IssuedRefreshToken
    {
        $value = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable(sprintf('+%d seconds', $this->ttl));

        $this->repository->save(new RefreshToken(
            user: $user,
            tokenHash: $this->hash($value),
            expiresAt: $expiresAt,
        ));

        return new IssuedRefreshToken(
            user: $user,
            value: $value,
            expiresAt: $expiresAt,
        );
    }

    /**
     * @throws RefreshTokenRejected when the cookie is missing or not shaped like a token
     */
    private function readToken(Request $request): string
    {
        $token = $request->cookies->getString($this->cookieName);
        if (preg_match(self::TOKEN_FORMAT, $token) !== 1) {
            throw new RefreshTokenRejected();
        }

        return $token;
    }

    /**
     * @return non-empty-string 64 hex characters
     */
    private function hash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->hashKey);
    }

    private function makeCookie(Request $request, string $value, DateTimeImmutable $expiresAt): Cookie
    {
        return Cookie::create($this->cookieName)
            ->withValue($value)
            ->withExpires($expiresAt)
            ->withPath($this->cookiePath)
            ->withSecure($request->isSecure())
            ->withHttpOnly()
            ->withSameSite(Cookie::SAMESITE_LAX);
    }
}
