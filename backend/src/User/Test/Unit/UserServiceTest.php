<?php

namespace App\User\Test\Unit;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

#[TestDox('Auth')]
class UserServiceTest extends TestCase
{
    private UserService $userService;

    public function setUp(): void
    {
        $this->userService = new UserService(
            $this->createStub(UserPasswordHasherInterface::class),
            $this->createStub(UserRepository::class),
            $this->createStub(Mailer::class),
            $this->createStub(RouterInterface::class),
        );
    }

    #[TestDox('Request email change exception')]
    public function testRequestEmailChangeException(): void
    {
        $user = self::createStub(User::class);
        $user->method('getEmail')->willReturn('sameEmail');
        self::expectException(LogicException::class);
        $this->userService->requestEmailChange($user, 'sameEmail');
    }

    #[TestDox('Send email change verification email exception')]
    public function testSendEmailChangeVerificationEmailException(): void
    {
        $user = self::createStub(User::class);
        $user->method('getEmailChange')->willReturn('email');
        $user->method('getEmailChangeCode')->willReturn(null);
        self::expectException(LogicException::class);
        $this->userService->sendEmailChangeVerificationEmail($user);
    }
}
