<?php

namespace App\SplitExpense\Http\V1\Request;

use App\Core\Enum\Group;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SeExpenseDto
{
    #[OA\Property(example: 1)]
    #[Assert\Positive]
    public ?int $paidByUserId = null;

    #[OA\Property(example: 1)]
    #[Assert\Positive]
    public ?int $categoryId = null;

    #[OA\Property(example: '100.0000')]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,4})?$/')]
    public string $amount;

    #[OA\Property(example: 'Dinner')]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Length(max: 255)]
    public string $title;

    #[OA\Property(example: 'Restaurant bill')]
    #[Assert\Length(max: 65535)]
    public ?string $description = null;

    #[OA\Property(example: 1)]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Positive]
    public int $currencyId;

    #[OA\Property(example: '2026-05-27')]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Date]
    public string $expenseDate;

    /** @var SeExpenseSplitDto[] */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/SeExpenseSplitDto'),
    )]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $splits = [];
}
