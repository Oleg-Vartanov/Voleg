<?php

namespace App\Core\ValueObject;

readonly class Secret
{
    public function __construct(
        public string $plain,
        public string $hash,
    ) {
    }
}
