<?php

namespace App\SplitExpense\Http\V1\Expense;

use App\Core\Validator\Constraints\IntRange;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class ExpenseSplitRequestPayload
{
    #[OA\Property(example: 1)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $userId;

    #[OA\Property(description: 'Amount in minor currency units (e.g. cents for USD)', example: 5000)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[IntRange]
    public int $amount;
}
