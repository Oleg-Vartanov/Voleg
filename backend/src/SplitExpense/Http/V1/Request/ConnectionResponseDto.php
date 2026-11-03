<?php

namespace App\SplitExpense\Http\V1\Request;

use App\SplitExpense\Enum\ConnectionStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ConnectionResponseDto
{
    #[Assert\NotBlank, Assert\Choice(callback: [ConnectionStatusEnum::class, 'cases'])]
    public ConnectionStatusEnum $status;
}
