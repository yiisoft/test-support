<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\EventDispatcher;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
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
            [
                DateTime::class => [
                    static function (object $param) use (&$listener1, $event) {
                        $listener1 = $param === $event;
                    },
                    static function (object $param) use (&$listener2, $event) {
                        $listener2 = $param === $event;
                    },
                ],
            ]
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
            [
                DateTime::class => [
                    static function (object $param) use (&$listener1, $event) {
                        $listener1 = $param === $event;
                        throw new RuntimeException('test');
                    },
                    static function (object $param) use (&$listener2, $event) {
                        $listener2 = $param === $event;
                    },
                ],
            ]
        );

        $this->expectException(RuntimeException::class);
        try {
            $dispatcher->dispatch($event);
        } catch (Throwable $e) {
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
            [
                StoppableEvent::class => [
                    static function () use (&$listener1) {
                        $listener1 = true;
                    },
                    static function () use (&$listener2) {
                        $listener2 = true;
                    },
                ],
            ]
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
            [
                StoppableEvent::class => [
                    static function (object $event) use (&$listener1) {
                        $listener1 = true;
                        $event->setPropagationStopped(true);
                    },
                    static function () use (&$listener2) {
                        $listener2 = true;
                    },
                ],
            ]
        );

        $dispatcher->dispatch($event);

        self::assertTrue($event->isPropagationStopped());
        self::assertTrue($listener1);
        self::assertFalse($listener2);
    }

    public function testIsClassTriggered(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher([]);

        $dispatcher->dispatch($event);

        self::assertTrue($dispatcher->isClassTriggered(DateTimeImmutable::class));
        self::assertFalse($dispatcher->isClassTriggered(DateTimeInterface::class));
    }

    public function testIsInstanceOfTriggered(): void
    {
        $event = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher([]);

        $dispatcher->dispatch($event);

        self::assertTrue($dispatcher->isInstanceOfTriggered(DateTimeImmutable::class));
        self::assertTrue($dispatcher->isInstanceOfTriggered(DateTimeInterface::class));
        self::assertFalse($dispatcher->isInstanceOfTriggered(DateTime::class));
    }

    public function testIsObjectTriggered(): void
    {
        $event = new DateTimeImmutable();
        $notEvent = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher([]);

        $dispatcher->dispatch($event);

        self::assertTrue($dispatcher->isObjectTriggered($event));
        self::assertFalse($dispatcher->isObjectTriggered($notEvent));
    }

    public function testGetEvents(): void
    {
        $event1 = new DateTimeImmutable();
        $event2 = new DateTimeImmutable();
        $event3 = new DateTimeImmutable();
        $dispatcher = $this->prepareDispatcher([]);

        $dispatcher->dispatch($event1);
        $dispatcher->dispatch($event2);
        $dispatcher->dispatch($event3);

        self::assertSame([$event1, $event2, $event3], $dispatcher->getEvents());
    }

    public function testGetEmptyEvents(): void
    {
        $dispatcher = $this->prepareDispatcher([]);

        self::assertSame([], $dispatcher->getEvents());
    }

    public function testDifferentListeners(): void
    {
        $event = new DateTimeImmutable();

        $listener1 = false;
        $listener2 = false;
        $listener3 = false;
        $listeners = [
            DateTimeImmutable::class => [
                static function () use (&$listener1) {
                    $listener1 = true;
                },
            ],
            DateTimeInterface::class => [
                static function () use (&$listener2) {
                    $listener2 = true;
                },
            ],
            DateTime::class => [
                static function () use (&$listener3) {
                    $listener3 = true;
                },
            ],
        ];

        $this->prepareDispatcher($listeners)->dispatch($event);

        self::assertTrue($listener1);
        self::assertTrue($listener2);
        self::assertFalse($listener3);
    }


    protected function prepareDispatcher(array $handlers): SimpleEventDispatcher
    {
        return new SimpleEventDispatcher($handlers);
    }
}
