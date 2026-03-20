<?php

namespace App\FixturePredictions\Http\V1\Predictions;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\User\Entity\User;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([PredictionsFiltersResponse::GROUP])]
class PredictionsFiltersResponse
{
    public const string GROUP = 'filters';

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

    /**
     * @var array<User>
     */
    public array $users;

    /**
     * @param array<User> $users
     */
    public function __construct(
        ?DateTimeImmutable $start,
        ?DateTimeImmutable $end,
        ?Competition $competitionEntity,
        ?Season $seasonEntity,
        int $limit,
        array $users,
    ) {
        $this->start = $start?->format('Y-m-d');
        $this->end = $end?->format('Y-m-d');
        $this->competition = $competitionEntity?->getCode();
        $this->season = $seasonEntity?->getYear();
        $this->limit = $limit;
        $this->users = $users;
    }
}
