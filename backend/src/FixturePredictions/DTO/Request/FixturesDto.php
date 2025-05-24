<?php

namespace App\FixturePredictions\DTO\Request;

use App\Core\Helper\Arr;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Fixtures Request')]
class FixturesDto
{
    public function __construct(
        #[OA\Property(example: '2024-12-31')]
        public ?DateTime $start = null,

        #[OA\Property(example: '2024-12-31')]
        public ?DateTime $end = null,

        #[Assert\Positive]
        public ?int $limit = null,

        #[Assert\NotBlank, Assert\Positive]
        public int $year = 2024,

        #[Assert\NotBlank, Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
        public string $competitionCode = CompetitionCodeEnum::EPL->value,

        /** @var int[] */
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Assert\All([new Assert\Type('int'), new Assert\Positive])]
        public array $userIds = [],
    ) {
        $this->userIds = Arr::castItemsToIntIfPossible($userIds);
        $this->start ??= (new DateTime())->modify('-5 days');
        $this->end ??= (new DateTime())->modify('+5 days');
        $this->start->setTime(0, 0, 0);
        $this->end->setTime(23, 59, 59);
    }
}