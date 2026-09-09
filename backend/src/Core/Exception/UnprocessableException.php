<?php

namespace App\Core\Exception;

use Exception;

class UnprocessableException extends Exception
{
    public function __construct(
        string $message,
        public string $propertyPath,
    ) {
        parent::__construct($message);
    }
}
