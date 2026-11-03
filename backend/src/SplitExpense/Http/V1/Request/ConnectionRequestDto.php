<?php

namespace App\SplitExpense\Http\V1\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ConnectionRequestDto
{
    #[Assert\NotBlank, Assert\Positive]
    public int $connectionUserId;
}
