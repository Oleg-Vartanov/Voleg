<?php

namespace App\FixturePredictions\Http\V1\Leaderboard;

use App\Core\Enum\Group;
use App\User\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
class LeaderboardRow
{
    public function __construct(
        public User $user,
        public ?int $totalPoints,
        public int $periodPoints,
    ) {
    }
}
