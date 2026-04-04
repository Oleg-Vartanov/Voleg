<?php

namespace App\FixturePredictions\Exception;

use Exception;
use Throwable;

class FixtureProviderClientException extends Exception
{
    public string $rawMessage = '';

    public function __construct(
        public int $statusCode,
        string $message = '',
        public string $body = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->rawMessage = $message;
        $message = $message . "\nStatus code: " . $statusCode . "\nBody: " . $body;
        parent::__construct($message, $code, $previous);
    }
}
