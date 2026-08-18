<?php

namespace App\SplitExpense\Http\V1\Request;

use App\SplitExpense\Entity\SeExpense;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SeExpenseSplitDto
{
    #[OA\Property(example: 1)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $userId;

    #[OA\Property(description: 'Amount in minor currency units (e.g. cents for USD)', example: 5000)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(value: SeExpense::MAX_AMOUNT, message: SeExpenseDto::MAX_AMOUNT_MSG)]
    public int $amount;
}
