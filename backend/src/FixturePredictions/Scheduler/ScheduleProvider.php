<?php

namespace App\FixturePredictions\Scheduler;

use App\Core\Scheduler\AbstractScheduleProvider;
use App\FixturePredictions\Messenger\SyncMessage;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

/**
 * @codeCoverageIgnore
 */
#[AsSchedule('fixture_predictions')]
final class ScheduleProvider extends AbstractScheduleProvider
{
    protected function createSchedule(): Schedule
    {
        return new Schedule()
            ->with(
                // At every 10th minute.
                RecurringMessage::cron(
                    '*/10 * * * *',
                    new RedispatchMessage(new SyncMessage(), 'fixture_predictions'),
                )
            );
    }
}
