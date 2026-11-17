<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\EventListener\JWTCreatedListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

#[TestDox('Auth')]
class JWTCreatedListenerTest extends TestCase
{
    #[TestDox('JWTCreatedListener: success')]
    public function testSuccess(): void
    {
        $user = self::createStub(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getUsername')->willReturn('testUsername');

        $listener = new JWTCreatedListener();

        $event = self::createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn($user);
        $event->method('getData')->willReturn([
            'roles' => ['ROLE_USER'],
        ]);
        $event->expects(self::once())->method('setData')->with([
            'roles' => ['ROLE_USER'],
            'id' => 1,
            'username' => 'testUsername',
        ]);

        $listener->onJWTCreated($event);
    }

    #[TestDox('JWTCreatedListener: invalid user')]
    public function testInvalidUser(): void
    {
        $listener = new JWTCreatedListener();

        $event = self::createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn(self::createStub(UserInterface::class));
        $event->expects(self::never())->method('getData');

        $listener->onJWTCreated($event);
    }
}
