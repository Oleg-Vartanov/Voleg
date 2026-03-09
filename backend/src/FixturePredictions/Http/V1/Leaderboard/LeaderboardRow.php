<?php

namespace App\FixturePredictions\Http\V1\Leaderboard;

use App\User\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([LeaderboardGetAction::GROUP])]
class LeaderboardRow
{
    public function __construct(
        public User $user,
        public int $totalPoints,
        public int $periodPoints,
    ) {
    }
}
