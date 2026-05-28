<?php

namespace App\SplitExpense\Http\V1\Request;

use App\Core\Enum\Group;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SeExpenseSplitDto
{
    #[OA\Property(example: 1)]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Positive]
    public int $userId;

    #[OA\Property(example: '25.5000')]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,4})?$/')]
    public string $amount;
}
