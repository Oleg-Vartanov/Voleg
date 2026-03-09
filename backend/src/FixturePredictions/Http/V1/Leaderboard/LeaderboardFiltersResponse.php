<?php

namespace App\FixturePredictions\Http\V1\Leaderboard;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([LeaderboardGetAction::GROUP])]
class LeaderboardFiltersResponse
{
    #[OA\Property(example: '2024-12-31')]
    public ?string $start;

    #[OA\Property(example: '2024-12-31')]
    public ?string $end;

    #[OA\Property(
        type: 'string',
        enum: [CompetitionCodeEnum::class, 'values'],
        example: CompetitionCodeEnum::EPL->value
    )]
    public ?string $competition;

    #[OA\Property(example: 2024)]
    public ?int $season;

    public int $limit;

    public function __construct(
        ?DateTimeImmutable $start,
        ?DateTimeImmutable $end,
        ?Competition $competitionEntity,
        ?Season $seasonEntity,
        int $limit,
    ) {
        $this->start = $start?->format('Y-m-d');
        $this->end = $end?->format('Y-m-d');
        $this->competition = $competitionEntity?->getCode();
        $this->season = $seasonEntity?->getYear();
        $this->limit = $limit;
    }
}
