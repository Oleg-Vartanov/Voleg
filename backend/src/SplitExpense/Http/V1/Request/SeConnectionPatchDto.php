<?php

namespace App\SplitExpense\Http\V1\Request;

use App\SplitExpense\Enum\SeConnectionStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

class SeConnectionPatchDto
{
    #[Assert\NotBlank]
    public SeConnectionStatusEnum $status;
}
