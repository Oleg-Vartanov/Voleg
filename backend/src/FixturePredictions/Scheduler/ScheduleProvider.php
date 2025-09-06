<?php

namespace App\FixturePredictions\Scheduler;

use App\FixturePredictions\Messenger\SyncMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * @codeCoverageIgnore
 */
#[AsSchedule('fixture_predictions')]
final class ScheduleProvider implements ScheduleProviderInterface
{
    private Schedule $schedule;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= (new Schedule())
            ->with(
                // At every 10th minute.
                RecurringMessage::cron('*/10 * * * *', new SyncMessage())
            );
    }
}
