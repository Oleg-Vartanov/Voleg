<?php

namespace App\User\Test\Unit;

use App\Core\Service\Mailer;
use App\Core\Service\TokenHasher;
use App\User\Model\Entity\User;
use App\User\Repository\EmailChangeTokenRepository;
use App\User\Repository\UserRepository;
use App\User\Service\EmailChangeService;
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
            self::createStub(Mailer::class),
            self::createStub(RouterInterface::class),
            new TokenHasher('test-key'),
            self::createStub(EmailChangeTokenRepository::class),
            self::createStub(UserRepository::class),
            3600,
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
