<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\EventListener\JWTCreatedListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

#[TestDox('Auth')]
class JWTCreatedListenerTest extends TestCase
{
    #[TestDox('JWTCreatedListener: success')]
    public function testSuccess(): void
    {
        $user = self::createStub(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getDisplayName')->willReturn('testName');
        $user->method('getTag')->willReturn('testTag');

        $security = self::createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $listener = new JWTCreatedListener($security);

        $event = self::createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn($user);
        $event->method('getData')->willReturn([]);
        $event->expects(self::once())->method('setData')->with([
            'id' => 1,
            'displayName' => 'testName',
            'tag' => 'testTag',
        ]);

        $listener->onJWTCreated($event);
    }

    #[TestDox('JWTCreatedListener: invalid user')]
    public function testInvalidUser(): void
    {
        $security = self::createStub(Security::class);
        $security->method('getUser')->willReturn(null);

        $listener = new JWTCreatedListener($security);

        $event = self::createMock(JWTCreatedEvent::class);
        $event->expects(self::never())->method('getData');

        $listener->onJWTCreated($event);
    }
}
