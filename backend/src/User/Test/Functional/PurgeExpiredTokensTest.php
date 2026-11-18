<?php

namespace App\User\Test\Functional;

use App\Core\Repository\AbstractEntityRepository;
use App\Core\Test\ApiTestCase;
use App\User\Entity\EmailChangeToken;
use App\User\Entity\PasswordResetToken;
use App\User\Entity\RefreshToken;
use App\User\Entity\User;
use App\User\Entity\VerificationToken;
use App\User\Messenger\PurgeExpiredTokensMessage;
use App\User\Repository\EmailChangeTokenRepository;
use App\User\Repository\PasswordResetTokenRepository;
use App\User\Repository\RefreshTokenRepository;
use App\User\Repository\VerificationTokenRepository;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Random\RandomException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Messenger\MessageBusInterface;

#[TestDox('Tokens')]
class PurgeExpiredTokensTest extends ApiTestCase
{
    /**
     * @throws ExceptionInterface|RandomException
     */
    #[TestDox('Purge removes expired tokens of every type and keeps the rest')]
    public function testPurgeRemovesOnlyExpiredTokens(): void
    {
        /** @var array<class-string<AbstractEntityRepository<object>>, Closure(User, DateTimeImmutable): object> $factories */
        $factories = [
            RefreshTokenRepository::class => static fn (User $user, DateTimeImmutable $expiresAt) =>
                new RefreshToken($user, self::randomHash(), $expiresAt),
            VerificationTokenRepository::class => static fn (User $user, DateTimeImmutable $expiresAt) =>
                new VerificationToken($user, self::randomHash(), $expiresAt),
            EmailChangeTokenRepository::class => static fn (User $user, DateTimeImmutable $expiresAt) =>
                new EmailChangeToken($user, self::randomHash(), 'new@example.com', $expiresAt),
            PasswordResetTokenRepository::class => static fn (User $user, DateTimeImmutable $expiresAt) =>
                new PasswordResetToken($user, self::randomHash(), $expiresAt),
        ];

        // Separate users, since most token types allow one per user.
        $ids = [];
        foreach ($factories as $repositoryClass => $factory) {
            $repository = $this->getService($repositoryClass);
            $expired = $factory($this->createUser(), new DateTimeImmutable('-1 second'));
            $active = $factory($this->createUser(), new DateTimeImmutable('+1 day'));
            $repository->save($expired);
            $repository->save($active, true);
            $ids[$repositoryClass] = [$expired->getId(), $active->getId()];
        }

        // Same message the schedule sends.
        $this->getService(MessageBusInterface::class)->dispatch(
            new RedispatchMessage(new PurgeExpiredTokensMessage(), 'default')
        );
        $this->getService('doctrine.orm.entity_manager')->clear();

        foreach ($ids as $repositoryClass => [$expiredId, $activeId]) {
            $repository = $this->getService($repositoryClass);
            self::assertNull($repository->find($expiredId), $repositoryClass . ': expired token kept');
            self::assertNotNull($repository->find($activeId), $repositoryClass . ': active token removed');
        }
    }

    /**
     * @throws RandomException
     */
    private static function randomHash(): string
    {
        return bin2hex(random_bytes(32));
    }
}
