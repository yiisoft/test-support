<?php

namespace Yiisoft\Test\Support\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class StaticClock implements ClockInterface
{

    public function __construct(
        private DateTimeImmutable $now,
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
