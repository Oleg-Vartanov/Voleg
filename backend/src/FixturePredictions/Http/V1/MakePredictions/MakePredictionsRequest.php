<?php

namespace App\FixturePredictions\Http\V1\MakePredictions;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Prediction')]
class MakePredictionsRequest
{
    #[Assert\NotBlank, Assert\Positive]
    public int $fixtureId;

    #[Assert\NotBlank, Assert\Range(min: 0, max: 99)]
    public int $homeScore;

    #[Assert\NotBlank, Assert\Range(min: 0, max: 99)]
    public int $awayScore;
}
