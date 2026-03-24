<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\Service\AuthService;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;

#[TestDox('Auth')]
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    public function setUp(): void
    {
        $this->authService = new AuthService(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(MailerInterface::class),
            $this->createStub(ParameterBagInterface::class),
            $this->createStub(RouterInterface::class),
            $this->createStub(UserService::class),
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
