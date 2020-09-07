<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\EventDispatcher;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TypeError;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use PHPUnit\Framework\TestCase;

class SimpleEventDispatcherTest extends TestCase
{
    public function testDispatcherClosure(): void
    {
        $event = new DateTime();
        $toReturn = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher(static fn(object $event) => $toReturn);

        $modified = $dispatcher->dispatch($event);

        $this->assertSame($toReturn, $modified);
    }

    public function testDispatcherClosureNotReturnsObject(): void
    {
        $event = new DateTime();
        $dispatcher = $this->prepareDispatcher(static fn(object $event) => null);

        $modified = $dispatcher->dispatch($event);

        $this->assertSame($event, $modified);
    }

    public function testIsClassTriggered(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isClassTriggered(DateTimeImmutable::class));
        $this->assertFalse($dispatcher->isClassTriggered(DateTimeInterface::class));
    }

    public function testIsInstanceOfTriggered(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeImmutable::class));
        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeInterface::class));
        $this->assertFalse($dispatcher->isInstanceOfTriggered(DateTime::class));
    }

    public function testIsObjectTriggered(): void
    {
        $event = new DateTimeImmutable();
        $notEvent = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isObjectTriggered($event));
        $this->assertFalse($dispatcher->isObjectTriggered($notEvent));
    }

    public function testGetEvents(): void
    {
        $event1 = new DateTimeImmutable();
        $event2 = new DateTimeImmutable();
        $event3 = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event1);
        $dispatcher->dispatch($event2);
        $dispatcher->dispatch($event3);

        $this->assertSame([$event1, $event2, $event3], $dispatcher->getEvents());
    }

    public function testGetEmptyEvents(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $this->assertSame([], $dispatcher->getEvents());
    }

    protected function prepareDispatcher(Closure $dispatcher = null): SimpleEventDispatcher
    {
        return new SimpleEventDispatcher($dispatcher);
    }
}
