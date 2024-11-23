<?php

namespace App\DTO\Fixtures;

use App\Enum\Fixtures\CompetitionCodeEnum;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Fixtures Request')]
class FixturesRequestDto
{
    #[Assert\Date]
    public mixed $start = null;

    #[Assert\Date]
    public mixed $end = null;

    #[Assert\NotBlank, Assert\Type('integer')]
    public mixed $year = 2024;

    #[Assert\NotBlank, Assert\Type('string')]
    public mixed $countryCode = CompetitionCodeEnum::EPL->value;
}