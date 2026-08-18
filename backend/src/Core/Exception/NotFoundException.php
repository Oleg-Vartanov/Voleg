<?php

namespace App\Core\Exception;

use Exception;
use Throwable;

class NotFoundException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        public ?string $tag = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
