<?php

namespace App\FixturePredictions\DTO\Request;

use App\FixturePredictions\Enum\CompetitionCodeEnum;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Sync request')]
class SyncDto
{
    #[Assert\NotBlank, Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
    public string $competitionCode;

    #[Assert\NotBlank, Assert\Range(min: 1970, max: 2200)]
    public int $seasonYear;
}
