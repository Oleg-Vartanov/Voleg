<?php

namespace App\User\Test\Functional;

use App\Core\Test\ApiTestCase;
use App\User\Entity\RefreshToken;
use App\User\Messenger\PurgeExpiredRefreshTokensMessage;
use App\User\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Random\RandomException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Messenger\MessageBusInterface;

#[TestDox('Refresh tokens')]
class PurgeExpiredRefreshTokensTest extends ApiTestCase
{
    /**
     * @throws ExceptionInterface|RandomException
     */
    #[TestDox('Purge removes expired tokens and keeps the rest')]
    public function testPurgeRemovesOnlyExpiredTokens(): void
    {
        $repository = $this->getService(RefreshTokenRepository::class);
        $user = $this->createUser();

        $expired = new RefreshToken($user, bin2hex(random_bytes(32)), new DateTimeImmutable('-1 second'));
        $active = new RefreshToken($user, bin2hex(random_bytes(32)), new DateTimeImmutable('+1 day'));
        $repository->save($expired);
        $repository->save($active, true);
        $expiredId = $expired->getId();
        $activeId = $active->getId();

        // Same message the schedule sends.
        $this->getService(MessageBusInterface::class)->dispatch(
            new RedispatchMessage(new PurgeExpiredRefreshTokensMessage(), 'default')
        );
        $this->getService('doctrine.orm.entity_manager')->clear();

        self::assertNull($repository->find($expiredId));
        self::assertNotNull($repository->find($activeId));
    }
}
