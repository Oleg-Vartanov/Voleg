<?php

namespace App\Core\Util;

use App\Core\ValueObject\Period;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;

class DateTimeUtil
{
    /**
     * Splits a date range into multiple consecutive periods using a given
     * interval.
     *
     * For example, given a 1-year range and a 3-month interval, this method
     * will return 4 Period objects, each spanning 3 months (the last one may
     * be shorter if needed).
     *
     * @param string $intervalDuration ISO_8601 duration.
     *
     * @return Period[]
     * @throws Exception
     */
    public static function getPeriods(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $intervalDuration = '',
    ): array {
        $interval = new DateInterval($intervalDuration);
        $period = new DatePeriod($from, $interval, $to);
        $dates = iterator_to_array($period);

        if (end($dates) != $to) {
            $dates[] = $to;
        }

        $periods = [];
        for ($i = 0; $i < count($dates) - 1; $i++) {
            $periods[] = new Period($dates[$i], $dates[$i + 1]);
        }

        return $periods;
    }
}
