<?php

namespace App\DTO\Fixtures\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Prediction')]
class PredictionDto
{
    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public int $fixtureId;

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Range(min: 0, max: 99)]
    public int $homeScore;

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Range(min: 0, max: 99)]
    public int $awayScore;
}