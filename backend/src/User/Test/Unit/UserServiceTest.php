<?php

namespace App\User\Test\Unit;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Repository\UserTokenRepository;
use App\User\Service\EmailChangeService;
use App\User\Service\UserTokenService;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

#[TestDox('Auth')]
class UserServiceTest extends TestCase
{
    private EmailChangeService $emailChangeService;

    public function setUp(): void
    {
        $this->emailChangeService = new EmailChangeService(
            $this->createStub(Mailer::class),
            $this->createStub(RouterInterface::class),
            $this->createStub(UserTokenService::class),
            $this->createStub(UserTokenRepository::class),
            $this->createStub(UserRepository::class),
        );
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    #[TestDox('Request email change exception')]
    public function testRequestEmailChangeException(): void
    {
        $user = self::createStub(User::class);
        $user->method('getEmail')->willReturn('sameEmail');
        self::expectException(LogicException::class);
        $this->emailChangeService->requestEmailChange($user, 'sameEmail');
    }
}
