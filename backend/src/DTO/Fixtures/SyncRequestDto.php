<?php

namespace App\DTO\Fixtures;

use App\Enum\Fixtures\CompetitionCodeEnum;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Sync request')]
class SyncRequestDto
{
    #[Assert\NotBlank,
        Assert\Type('string'),
        Assert\Choice(callback: [CompetitionCodeEnum::class, 'values'])]
    #[OA\Property(type: 'string', example: CompetitionCodeEnum::EPL->value)]
    public mixed $competitionCode;

    #[Assert\NotBlank,
        Assert\Type('integer'),
        Assert\Range(min: 1970, max: 2100)]
    #[OA\Property(type: 'integer', example: '2024')]
    public mixed $seasonYear;
}