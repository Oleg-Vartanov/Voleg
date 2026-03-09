<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\Security\UserChecker;
use Error;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

#[TestDox('UserChecker')]
class UserCheckerTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[TestDox('Pre auth: verified user')]
    public function testPreAuthVerifiedUser(): void
    {
        $checker = new UserChecker();
        $user = $this->createMock(User::class);
        $user->method('isVerified')->willReturn(true);

        try {
            $checker->checkPreAuth($user);
        } catch (CustomUserMessageAccountStatusException $e) {
            $this->fail('Expected no CustomUserMessageAccountStatusException to be thrown');
        }
        $this->assertTrue(true);
    }

    /**
     * @throws Exception
     */
    #[TestDox('Pre auth: unverified user')]
    public function testPreAuthUnverifiedUser(): void
    {
        $checker = new UserChecker();
        $user = $this->createMock(User::class);
        $user->method('isVerified')->willReturn(false);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($user);
    }

    /**
     * @throws Exception
     */
    #[TestDox('Pre auth: incorrect user')]
    public function testPreAuthIncorrectUser(): void
    {
        $checker = new UserChecker();
        $user = $this->createMock(UserInterface::class);

        try {
            $checker->checkPreAuth($user);
        } catch (Error $e) {
            $this->fail($e->getMessage());
        }
        $this->assertTrue(true);
    }
}
