<?php

namespace App\FixturePredictions\Model\DTO\Provider;

class TeamDto
{
    public function __construct(
        public int $providerTeamId,
        public string $name,
    ) {
    }
}
