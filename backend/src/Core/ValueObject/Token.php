<?php

namespace App\Core\ValueObject;

readonly class Token
{
    public function __construct(
        public string $plain,
        public string $hash,
    )
    {
    }
}