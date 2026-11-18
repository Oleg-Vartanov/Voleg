<?php

namespace App\User\Messenger;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('default')]
readonly class PurgeExpiredTokensMessage
{
}
