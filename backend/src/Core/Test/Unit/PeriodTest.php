<?php

namespace App\Core\Test\Unit;

use App\Core\Model\ValueObject\Period;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Period')]
class PeriodTest extends TestCase
{
    #[TestDox('Accepts an end after or equal to the start')]
    public function testValid(): void
    {
        $start = new DateTimeImmutable('2026-01-01');
        $end = new DateTimeImmutable('2026-04-01');

        $period = new Period($start, $end);
        self::assertSame($start, $period->getStart());
        self::assertSame($end, $period->getEnd());

        $instant = new Period($start, $start);
        self::assertSame($start, $instant->getEnd());
    }

    #[TestDox('Rejects an end before the start')]
    public function testEndBeforeStart(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Period(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-01-01'));
    }
}
