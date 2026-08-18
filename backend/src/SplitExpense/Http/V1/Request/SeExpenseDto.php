<?php

namespace App\SplitExpense\Http\V1\Request;

use App\SplitExpense\Entity\SeExpense;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SeExpenseDto
{
    public const string MAX_AMOUNT_MSG = 'This value ({{ value }}) should be less than or equal to {{ compared_value }}.';

    #[OA\Property(example: 1)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $paidByUserId;

    #[OA\Property(example: 1)]
    #[Assert\Positive]
    public ?int $categoryId = null;

    #[OA\Property(description: 'Amount in minor currency units (e.g. cents for USD)', example: 10000)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(value: SeExpense::MAX_AMOUNT, message: self::MAX_AMOUNT_MSG)]
    public int $amount;

    #[OA\Property(example: 'Dinner')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    #[OA\Property(example: 'Restaurant bill')]
    #[Assert\Length(max: 65535)]
    public ?string $description = null;

    #[OA\Property(example: 1)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $currencyId;

    #[OA\Property(example: '2026-05-27')]
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $expenseDate;

    /** @var SeExpenseSplitDto[] */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: SeExpenseSplitDto::class))
    )]
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $splits = [];
}
