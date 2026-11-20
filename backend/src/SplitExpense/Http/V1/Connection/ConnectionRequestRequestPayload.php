<?php

namespace App\SplitExpense\Http\V1\Connection;

use Symfony\Component\Validator\Constraints as Assert;

class ConnectionRequestRequestPayload
{
    #[Assert\NotBlank, Assert\Positive]
    public int $connectionUserId;
}
