<?php

namespace App\FixturePredictions\Http\V1\Sync;

use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Sync request')]
class SyncRequest
{
    #[Assert\NotBlank, Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
    public string $competitionCode;

    #[Assert\NotBlank, Assert\Range(min: 1970, max: 2200)]
    public int $seasonYear;

    #[OA\Property(example: '2023-12-31T20:30:00+0400')]
    #[Assert\NotBlank]
    public DateTimeImmutable $from;

    #[OA\Property(example: '2025-01-01T20:30:00+0400')]
    #[Assert\NotBlank]
    public DateTimeImmutable $to;
}
