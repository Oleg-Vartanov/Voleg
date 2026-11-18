<?php

namespace App\Core\Service;

use App\Core\ValueObject\Secret;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Tokens are single 256-bit random values. Only their keyed hash (HMAC-SHA256)
 * is stored, and every lookup hashes the presented value first, so a leaked
 * hash is not usable as a token. A fast hash is safe here only because the
 * token is long and random.
 */
readonly class TokenHasher
{
    /** 64 lowercase hex characters. */
    public const string FORMAT = '/^[a-f0-9]{64}$/';

    public function __construct(
        #[Autowire(env: 'TOKEN_HASH_KEY')]
        private string $key,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function generate(): Secret
    {
        $plain = bin2hex(random_bytes(32));

        return new Secret(
            plain: $plain,
            hash: $this->hash($plain),
        );
    }

    /**
     * @return non-empty-string 64 hex characters
     */
    public function hash(string $token): string
    {
        return hash_hmac('sha256', $token, $this->key);
    }
}
