<?php

namespace App\DTO\Fixtures\Request;

use App\Enum\Fixtures\CompetitionCodeEnum;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Fixtures Request')]
class FixturesDto
{
    #[OA\Property(example: '2024-12-31')]
    #[Assert\Date]
    public ?string $start = null;

    #[OA\Property(example: '2024-12-31')]
    #[Assert\Date]
    public ?string $end = null;

    #[Assert\Type('digit')]
    public int $limit;

    #[OA\Property(example: '2024')]
    #[Assert\NotBlank, Assert\Type('integer')]
    public int $year = 2024;

    #[OA\Property(example: CompetitionCodeEnum::EPL->value)]
    #[Assert\NotBlank, Assert\Type('string')]
    public string $competitionCode = CompetitionCodeEnum::EPL->value;

    #[OA\Property(type: 'array', items: new OA\Items(type: 'integer'))]
    #[Assert\All([new Assert\Type('digit')])]
    public array $userIds = [];

    public function transform(): void
    {
        $this->start = $this->start === null ? (new DateTime())->modify('-5 days') : new DateTime($this->start);
        $this->end = $this->end === null ? (new DateTime())->modify('+5 days') : new DateTime($this->end);
        $this->start->setTime(0, 0, 0);
        $this->end->setTime(23, 59, 59);
    }
}