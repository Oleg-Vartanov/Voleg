<?php

namespace App\User\Scheduler;

use App\Core\Scheduler\AbstractScheduleProvider;
use App\User\Messenger\PurgeExpiredRefreshTokensMessage;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

/**
 * @codeCoverageIgnore
 */
#[AsSchedule('user')]
final class ScheduleProvider extends AbstractScheduleProvider
{
    protected function createSchedule(): Schedule
    {
        return new Schedule()
            ->with(
                // Every day at 04:00.
                RecurringMessage::cron(
                    '0 4 * * *',
                    new RedispatchMessage(new PurgeExpiredRefreshTokensMessage(), 'default'),
                )
            );
    }
}
