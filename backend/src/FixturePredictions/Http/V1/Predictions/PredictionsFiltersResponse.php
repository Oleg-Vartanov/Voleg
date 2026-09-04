<?php

namespace App\FixturePredictions\Http\V1\Predictions;

use App\Core\Enum\Group;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\User\Entity\User;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
class PredictionsFiltersResponse
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

    public int $offset;

    /**
     * Total number of fixtures matching the filters, ignoring limit and offset.
     */
    public int $total;

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
        int $offset,
        int $total,
        array $users,
    ) {
        $this->start = $start?->format('Y-m-d');
        $this->end = $end?->format('Y-m-d');
        $this->competition = $competitionEntity?->getCode();
        $this->season = $seasonEntity?->getYear();
        $this->limit = $limit;
        $this->offset = $offset;
        $this->total = $total;
        $this->users = $users;
    }
}
