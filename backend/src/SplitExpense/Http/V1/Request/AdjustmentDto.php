<?php

namespace App\SplitExpense\Http\V1\Request;

use App\Core\Validator\Constraints\IntRange;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class AdjustmentDto
{
    #[OA\Property(example: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $otherUserId;

    #[OA\Property(
        description: 'Signed minor units from the creator\'s view.',
        example: 5000,
    )]
    #[Assert\NotBlank]
    #[Assert\NotEqualTo(0)]
    #[IntRange]
    public int $amount;

    #[OA\Property(example: 1)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $currencyId;

    #[OA\Property(example: '2026-05-27')]
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $adjustmentDate;

    #[OA\Property(example: 'Settled in cash')]
    #[Assert\Length(max: 65535)]
    public ?string $description = null;
}
