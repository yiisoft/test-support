<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\EventDispatcher;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\Test\Support\Tests\EventDispatcher\Stub\StoppableEvent;

class SimpleEventDispatcherTest extends TestCase
{
    public function testListeners(): void
    {
        $event = new DateTime();
        $listener1 = false;
        $listener2 = false;
        $dispatcher = $this->prepareDispatcher(
            static function (object $param) use (&$listener1, $event) {
                $listener1 = $param === $event;
            },
            static function (object $param) use (&$listener2, $event) {
                $listener2 = $param === $event;
            }
        );

        $dispatcher->dispatch($event);

        self::assertTrue($listener1);
        self::assertTrue($listener2);
    }

    public function testListenerThrowsException(): void
    {
        $event = new DateTime();
        $listener1 = false;
        $listener2 = false;
        $dispatcher = $this->prepareDispatcher(
            static function (object $param) use (&$listener1, $event) {
                $listener1 = $param === $event;
                throw new RuntimeException();
            },
            static function (object $param) use (&$listener2, $event) {
                $listener2 = $param === $event;
            }
        );

        $this->expectException(RuntimeException::class);
        try {
            $dispatcher->dispatch($event);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            self::assertTrue($listener1);
            self::assertFalse($listener2);
        }
    }

    public function testListenersAndStoppedEvent(): void
    {
        $event = new StoppableEvent(true);
        $listener1 = false;
        $listener2 = false;
        $dispatcher = $this->prepareDispatcher(
            static function (object $event) use (&$listener1) {
                $listener1 = true;
            },
            static function (object $event) use (&$listener2) {
                $listener2 = true;
            }
        );

        $dispatcher->dispatch($event);

        self::assertTrue($event->isPropagationStopped());
        self::assertFalse($listener1);
        self::assertFalse($listener2);
    }

    public function testListenersAndStoppableEvent(): void
    {
        $event = new StoppableEvent(false);
        $listener1 = false;
        $listener2 = false;
        $dispatcher = $this->prepareDispatcher(
            static function (object $event) use (&$listener1) {
                $listener1 = true;
                $event->setPropagationStopped(true);
            },
            static function (object $event) use (&$listener2) {
                $listener2 = true;
            }
        );

        $dispatcher->dispatch($event);

        self::assertTrue($event->isPropagationStopped());
        self::assertTrue($listener1);
        self::assertFalse($listener2);
    }

    public function testCheckEventTriggeredBadTimesArgument(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $this->expectException(InvalidArgumentException::class);

        $dispatcher->isClassTriggered(DateTimeImmutable::class, -1);
    }

    public function testIsClassTriggered(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isClassTriggered(DateTimeImmutable::class));
        $this->assertFalse($dispatcher->isClassTriggered(DateTimeInterface::class));
    }

    public function testIsClassTriggeredMultiple(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);
        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isClassTriggered(DateTimeImmutable::class, 2));
    }

    public function testIsClassTriggeredMultipleFail(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertFalse($dispatcher->isClassTriggered(DateTimeImmutable::class, 2));
    }

    public function testIsInstanceOfTriggered(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch(new DateTimeImmutable());

        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeImmutable::class));
        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeInterface::class));
        $this->assertFalse($dispatcher->isInstanceOfTriggered(DateTime::class));
    }

    public function testIsInstanceOfTriggeredMultiple(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch(new DateTimeImmutable());
        $dispatcher->dispatch(new DateTimeImmutable());

        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeImmutable::class, 2));
        $this->assertTrue($dispatcher->isInstanceOfTriggered(DateTimeInterface::class, 2));
    }

    public function testIsInstanceOfTriggeredMultipleFail(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch(new DateTimeImmutable());

        $this->assertFalse($dispatcher->isInstanceOfTriggered(DateTimeImmutable::class, 2));
        $this->assertFalse($dispatcher->isInstanceOfTriggered(DateTimeInterface::class, 2));
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

    public function testIsObjectTriggeredMultiple(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);
        $dispatcher->dispatch($event);

        $this->assertTrue($dispatcher->isObjectTriggered($event, 2));
    }

    public function testIsObjectTriggeredMultipleFail(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch($event);

        $this->assertFalse($dispatcher->isObjectTriggered($event, 2));
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

    public function testGetClassesEvents(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $dispatcher->dispatch(new stdClass());
        $dispatcher->dispatch(new DateTimeImmutable());
        $dispatcher->dispatch(new DateTime());

        self::assertSame([
            stdClass::class,
            DateTimeImmutable::class,
            DateTime::class,
        ], $dispatcher->getClassesEvents());
    }

    public function testGetEmptyEvents(): void
    {
        $dispatcher = $this->prepareDispatcher();

        $this->assertSame([], $dispatcher->getEvents());
    }

    public function testClear(): void
    {
        $dispatcher = $this->prepareDispatcher();
        $dispatcher->dispatch(new DateTimeImmutable());
        $dispatcher->clearEvents();

        self::assertEmpty($dispatcher->getEvents());
    }

    protected function prepareDispatcher(Closure ...$dispatcher): SimpleEventDispatcher
    {
        return new SimpleEventDispatcher(...$dispatcher);
    }
}
