<?php

namespace App\Core\Scheduler;

use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * Only enqueues messages. The work runs from the queue, so it gets retries and
 * a failed-message record (see supervisord.conf).
 *
 * @codeCoverageIgnore
 */
abstract class AbstractScheduleProvider implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= $this->createSchedule();
    }

    abstract protected function createSchedule(): Schedule;
}
