<?php

namespace App\DTO\Fixtures;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class PredictionDto
{
    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public mixed $fixtureId;

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Range(min: 0, max: 99)]
    public mixed $homeScore;

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Range(min: 0, max: 99)]
    public mixed $awayScore;
}