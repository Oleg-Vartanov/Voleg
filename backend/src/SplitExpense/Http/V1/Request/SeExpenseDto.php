<?php

namespace App\SplitExpense\Http\V1\Request;

use App\Core\Enum\Group;
use Nelmio\ApiDocBundle\Attribute\Model;
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

    #[OA\Property(description: 'Amount in minor currency units (e.g. cents for USD)', example: 10000)]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Positive]
    public int $amount;

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
        items: new OA\Items(ref: new Model(type: SeExpenseSplitDto::class))
    )]
    #[Assert\NotBlank(groups: [Group::create->value])]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $splits = [];
}
