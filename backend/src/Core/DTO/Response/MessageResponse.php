<?php

namespace App\Core\DTO\Response;

readonly class MessageResponse
{
    public function __construct(
        public string $message = '',
    ) {
    }
}
