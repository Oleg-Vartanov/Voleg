<?php

namespace App\FixturePredictions\DTO\Provider;

class TeamDto
{
    public function __construct(
        public int $providerTeamId,
        public string $name,
    ) {
    }
}
