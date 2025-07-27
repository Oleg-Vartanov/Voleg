<?php

namespace App\FixturePredictions\Exception;

use Exception;
use Throwable;

class FixtureHasStartedException extends Exception
{
    public function __construct(
        string $message = "Fixture has already started.",
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
