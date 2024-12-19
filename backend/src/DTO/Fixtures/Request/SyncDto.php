<?php

namespace App\DTO\Fixtures\Request;

use App\Enum\Fixtures\CompetitionCodeEnum;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Sync request')]
class SyncDto
{
    #[OA\Property(example: CompetitionCodeEnum::EPL->value)]
    #[Assert\NotBlank,
        Assert\Type('string'),
        Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
    public string $competitionCode;

    #[OA\Property(example: '2024')]
    #[Assert\NotBlank,
        Assert\Type('integer'),
        Assert\Range(min: 1970, max: 2100)]
    public int $seasonYear;
}