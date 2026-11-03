<?php

namespace App\SplitExpense\Http\V1\Feed;

use App\SplitExpense\Enum\FeedTypeEnum;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Split Expense Feed Request')]
class FeedRequest
{
    private const int MAX_LIMIT = 100;

    /**
     * @param FeedTypeEnum[] $types The serializer reads this type: query strings become enums,
     *                                and unknown values are rejected before validation. The Type
     *                                constraint also covers the DTO being built by hand.
     */
    public function __construct(
        #[Assert\PositiveOrZero]
        public int $offset = 0,
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(self::MAX_LIMIT)]
        public int $limit = 100,
        #[OA\Property(
            description: 'Record types to include; all types when omitted.',
            type: 'array',
            items: new OA\Items(type: 'string', enum: ['expense', 'adjustment']),
        )]
        #[Assert\All([new Assert\Type(FeedTypeEnum::class)])]
        public array $types = [],
    ) {
    }

    /**
     * @return FeedTypeEnum[]
     */
    public function selectedTypes(): array
    {
        if ($this->types === []) {
            return FeedTypeEnum::cases();
        }

        // Keyed by value to drop duplicates, which would otherwise double the total count.
        $selected = [];
        foreach ($this->types as $type) {
            $selected[$type->value] = $type;
        }

        return $selected;
    }
}
