<?php

namespace App\FixturePredictions\Messenger;

use App\FixturePredictions\Enum\CompetitionCodeEnum;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('default')]
class SyncMessage
{
    public function __construct(
        public CompetitionCodeEnum $competition = CompetitionCodeEnum::EPL,
        public ?int $year = null,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
    ) {
    }
}
