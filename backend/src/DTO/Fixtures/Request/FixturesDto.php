<?php

namespace App\DTO\Fixtures\Request;

use App\Enum\Fixtures\CompetitionCodeEnum;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Fixtures Request')]
class FixturesDto
{
    #[Assert\Date]
    public mixed $start = null;

    #[Assert\Date]
    public mixed $end = null;

    #[Assert\Type('digit')]
    public mixed $limit;

    #[Assert\NotBlank, Assert\Type('integer')]
    public mixed $year = 2024;

    #[Assert\NotBlank, Assert\Type('string')]
    public mixed $competitionCode = CompetitionCodeEnum::EPL->value;

    #[Assert\All([new Assert\Type('digit')])]
    public mixed $userIds = [];

    public function transform(): void
    {
        $this->start = $this->start === null ? (new DateTime())->modify('-5 days') : new DateTime($this->start);
        $this->end = $this->end === null ? (new DateTime())->modify('+5 days') : new DateTime($this->end);
        $this->start->setTime(0, 0, 0);
        $this->end->setTime(0, 0, 0);
    }
}