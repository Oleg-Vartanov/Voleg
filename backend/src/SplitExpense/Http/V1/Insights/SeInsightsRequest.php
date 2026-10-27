<?php

namespace App\SplitExpense\Http\V1\Insights;

use App\Core\Util\ArrayUtil;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Insights Request')]
class SeInsightsRequest
{
    /**
     * @param mixed[] $userIds Extra payers whose expense totals are included when the current user is on the split.
     */
    public function __construct(
        #[Assert\PositiveOrZero]
        public int $offset = 0,
        #[Assert\Positive]
        public int $limit = 6,
        #[Assert\Positive]
        public ?int $categoryId = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Assert\All([new Assert\Type('int'), new Assert\Positive()])]
        public array $userIds = [],
    ) {
        $this->userIds = ArrayUtil::castItemsToIntIfPossible($userIds);
    }
}
