<?php

namespace App\User\Test\Unit;

use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Auth')]
class UserTokenTest extends TestCase
{
    #[TestDox('UserToken: isExpired true')]
    public function testIsExpiredTrue(): void
    {
        $token = $this->createToken(expiresAt: '-1 hour');
        self::assertTrue($token->isExpired());
    }

    #[TestDox('UserToken: isExpired false')]
    public function testIsExpiredFalse(): void
    {
        $token = $this->createToken();
        self::assertFalse($token->isExpired());
    }

    #[TestDox('UserToken: getEmailChange success')]
    public function testGetEmailChangeSuccess(): void
    {
        $token = $this->createToken(
            type: UserTokenTypeEnum::EMAIL_CHANGE,
            payload: ['emailChange' => 'new@example.com'],
        );
        self::assertSame('new@example.com', $token->getEmailChange());
    }

    #[TestDox('UserToken: getEmailChange wrong type')]
    public function testGetEmailChangeWrongType(): void
    {
        $token = $this->createToken();
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Token is not an email change token.');
        $token->getEmailChange();
    }

    #[TestDox('UserToken: getEmailChange no payload')]
    public function testGetEmailChangeNoPayload(): void
    {
        $token = $this->createToken(type: UserTokenTypeEnum::EMAIL_CHANGE);

        self::expectException(LogicException::class);
        self::expectExceptionMessage(
            'Token payload does not contain an email change token.'
        );

        $token->getEmailChange();
    }

    #[TestDox('UserToken: getEmailChange not string')]
    public function testGetEmailChangeNotString(): void
    {
        $token = $this->createToken(
            type: UserTokenTypeEnum::EMAIL_CHANGE,
            payload: ['emailChange' => 12345],
        );

        self::expectException(LogicException::class);
        self::expectExceptionMessage(
            'Token payload does not contain an email change token.'
        );

        $token->getEmailChange();
    }

    private function createToken(
        UserTokenTypeEnum $type = UserTokenTypeEnum::PASSWORD_RESET,
        string $expiresAt = '+1 hour',
        array $payload = [],
    ): UserToken {
        return new UserToken(
            type: $type,
            user: self::createStub(User::class),
            selector: 'selector',
            secret: 'secret',
            expiresAt: new DateTimeImmutable($expiresAt),
            payload: $payload,
        );
    }
}
