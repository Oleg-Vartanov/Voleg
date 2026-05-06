<?php

namespace App\Core\Service;

use App\Core\ValueObject\Token;
use Random\RandomException;

class TokenService
{
    /**
     * @throws RandomException
     */
    public function generate(): Token
    {
        $token = bin2hex(random_bytes(32));

        return new Token(
            plain: $token,
            hash: password_hash($token, PASSWORD_DEFAULT),
        );
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}