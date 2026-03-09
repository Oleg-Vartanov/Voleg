<?php

namespace App\Core\Http\Response;

readonly class MessageResponse
{
    public function __construct(
        public string $message = '',
    ) {
    }
}
