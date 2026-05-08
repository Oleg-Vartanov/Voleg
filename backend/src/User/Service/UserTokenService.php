<?php

namespace App\User\Service;

use App\Core\ValueObject\Secret;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use DateTimeImmutable;
use Random\RandomException;

class UserTokenService
{
    public const string TOKEN_EXPIRE_TIME = '+30 minutes';

    /**
     * @param mixed[] $payload
     * @return array{UserToken, Secret}
     * @throws RandomException
     */
    public function createToken(
        UserTokenTypeEnum $type,
        User $user,
        array $payload = [],
    ): array {
        $secret = $this->generateSecret();
        $token = new UserToken(
            type: $type,
            user: $user,
            selector: $this->generateSelector(),
            secret: $secret->hash,
            expiresAt: $this->createExpiresAt(),
            payload: $payload,
        );

        return [$token, $secret];
    }

    /**
     * @throws RandomException
     */
    public function generateSecret(): Secret
    {
        $secret = $this->generatePlainSecret();

        return new Secret(
            plain: $secret,
            hash: password_hash($secret, PASSWORD_DEFAULT),
        );
    }

    public function verifySecret(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * @throws RandomException
     */
    public function generatePlainSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws RandomException
     */
    public function generateSelector(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function createExpiresAt(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::TOKEN_EXPIRE_TIME);
    }
}
