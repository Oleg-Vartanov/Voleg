<?php

namespace App\Exception;

use Exception;

class FixtureHasStartedException extends Exception
{
    public function __construct($message = "Fixture has already started.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}