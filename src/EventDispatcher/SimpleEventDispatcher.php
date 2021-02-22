<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\EventDispatcher;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

use function get_class;

final class SimpleEventDispatcher implements EventDispatcherInterface
{
    /** @var array<int, Closure> */
    private array $listeners;

    /** @var object[] */
    private array $events = [];

    /**
     * @param Closure ...$listeners Functions that will handle each event.
     */
    public function __construct(Closure ...$listeners)
    {
        $this->listeners = $listeners;
    }

    public function dispatch(object $event): object
    {
        $this->events[] = $event;
        foreach ($this->listeners as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }
        return $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getEventClasses(): array
    {
        return array_map(
            static fn ($event) => get_class($event),
            $this->events
        );
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }

    public function isObjectTriggered(object $event, int $times = null): bool
    {
        return $this->processBoolResult(static fn (object $e): bool => $e === $event, $times);
    }

    public function isClassTriggered(string $class, int $times = null): bool
    {
        return $this->processBoolResult(static fn (object $event): bool => get_class($event) === $class, $times);
    }

    public function isInstanceOfTriggered(string $class, int $times = null): bool
    {
        return $this->processBoolResult(static fn (object $event): bool => $event instanceof $class, $times);
    }

    private function processBoolResult(Closure $closure, ?int $times): bool
    {
        if ($times < 0) {
            throw new \InvalidArgumentException('The $times argument cannot be less than zero.');
        }
        if ($times === null) {
            return $this->hasEvent($closure);
        }
        return $times === $this->calcEvent($closure);
    }

    private function hasEvent(Closure $closure): bool
    {
        foreach ($this->events as $event) {
            if ($closure($event)) {
                return true;
            }
        }
        return false;
    }

    private function calcEvent(Closure $closure): int
    {
        $count = 0;
        foreach ($this->events as $event) {
            if ($closure($event)) {
                ++$count;
            }
        }
        return $count;
    }
}
