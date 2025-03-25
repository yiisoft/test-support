<?php

namespace Yiisoft\Test\Support\Tests\Clock;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Clock\StaticClock;

final class StaticClockTest extends TestCase
{
    public function testClock(): void
    {
        $now = new DateTimeImmutable();

        $clock = new StaticClock($now);
        $this->assertEquals($now, $clock->now());

        // Assert that the clock did not change
        usleep(200000);
        $this->assertEquals($now, $clock->now());
    }
}
