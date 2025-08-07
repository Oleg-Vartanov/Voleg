<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\Factory\UserFactory;
use App\User\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;

#[TestDox('Auth')]
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->authService = new AuthService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(MailerInterface::class),
            $this->createMock(ParameterBagInterface::class),
            $this->createMock(RouterInterface::class),
            $this->createMock(UserFactory::class),
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[TestDox('Send verification email on verified user')]
    public function testSendVerificationEmailOnVerifiedUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isVerified')->willReturn(true);

        $this->expectException(LogicException::class);
        $this->authService->sendVerificationEmail($user);
    }
}
