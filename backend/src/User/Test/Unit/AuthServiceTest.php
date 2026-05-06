<?php

namespace App\User\Test\Unit;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\AuthService;
use App\User\Service\UserService;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

#[TestDox('Auth')]
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    public function setUp(): void
    {
        $this->authService = new AuthService(
            $this->createStub(Mailer::class),
            $this->createStub(RouterInterface::class),
            $this->createStub(UserService::class),
            $this->createStub(UserRepository::class),
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[TestDox('Try to send verification email on verified user')]
    public function testSendVerificationEmailOnVerifiedUser(): void
    {
        $user = $this->createStub(User::class);
        $user->method('isVerified')->willReturn(true);

        $this->expectException(LogicException::class);
        $this->authService->sendVerificationEmail($user);
    }
}
