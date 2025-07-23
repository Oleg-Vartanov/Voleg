<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\Service\AuthService;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[TestDox('Auth')]
class AuthServiceTest extends WebTestCase
{
    private AuthService $authService;

    public function setUp(): void
    {
        $container = static::getContainer();
        /** @var AuthService $authService */
        $authService = $container->get(AuthService::class);
        $this->authService = $authService;
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
