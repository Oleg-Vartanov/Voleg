<?php

namespace App\Core\Model\ValueObject;

use SensitiveParameter;

readonly class Secret
{
    public function __construct(
        #[SensitiveParameter]
        public string $plain,
        public string $hash,
    ) {
    }
}
