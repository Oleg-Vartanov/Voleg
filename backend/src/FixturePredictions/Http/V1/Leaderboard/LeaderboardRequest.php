<?php

namespace App\FixturePredictions\Http\V1\Leaderboard;

use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Leaderboard Request')]
class LeaderboardRequest
{
    public function __construct(
        #[OA\Property(example: '2024-12-31')]
        public ?DateTimeImmutable $start = null,
        #[OA\Property(example: '2024-12-31')]
        public ?DateTimeImmutable $end = null,
        #[Assert\Positive]
        public int $limit = 50,
        #[Assert\Positive]
        public ?int $season = null,
        public bool $defaultToCurrentSeason = false,
        #[Assert\NotBlank, Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
        public string $competitionCode = CompetitionCodeEnum::EPL->value,
    ) {
        $this->start ??= new DateTimeImmutable()->modify('-5 days');
        $this->end ??= new DateTimeImmutable()->modify('+5 days');
        $this->start = $this->start->setTime(0, 0, 0);
        $this->end = $this->end->setTime(23, 59, 59);
    }
}
