<?php

namespace App\SplitExpense\Http\V1\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SeConnectionCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $connectionUserId;
}
