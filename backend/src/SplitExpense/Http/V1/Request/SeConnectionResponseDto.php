<?php

namespace App\SplitExpense\Http\V1\Request;

use App\SplitExpense\Enum\SeConnectionStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

class SeConnectionResponseDto
{
    #[Assert\NotBlank, Assert\Choice(callback: [SeConnectionStatusEnum::class, 'cases'])]
    public SeConnectionStatusEnum $status;
}
