<?php

namespace App\FixturePredictions\DTO\Request;

use App\Core\Util\ArrayUtil;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTime;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Fixtures Request')]
class FixturesDto
{
    public function __construct(
        #[OA\Property(example: '2024-12-31')]
        public ?DateTimeImmutable $start = null,
        #[OA\Property(example: '2024-12-31')]
        public ?DateTimeImmutable $end = null,
        #[Assert\Positive]
        public int $limit = 50,
        #[Assert\NotBlank, Assert\Positive]
        public ?int $season = null,
        #[Assert\NotBlank, Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
        public string $competitionCode = CompetitionCodeEnum::EPL->value,
        /** @var int[] */
        #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
        #[Assert\All([new Assert\Type('int'), new Assert\Positive()])]
        public array $userIds = [],
    ) {
        $this->userIds = ArrayUtil::castItemsToIntIfPossible($userIds);
        $this->start ??= (new DateTimeImmutable())->modify('-5 days');
        $this->end ??= (new DateTimeImmutable())->modify('+5 days');
        $this->start = $this->start->setTime(0, 0, 0);
        $this->end = $this->end->setTime(23, 59, 59);
    }
}
