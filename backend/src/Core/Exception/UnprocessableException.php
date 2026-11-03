<?php

namespace App\Core\Exception;

use Exception;

class UnprocessableException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $propertyPath,
    ) {
        parent::__construct($message);
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }
}
