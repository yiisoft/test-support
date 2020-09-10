<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\EventDispatcher;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final class SimpleEventDispatcher implements EventDispatcherInterface
{
    private iterable $listeners;

    /** @var object[] */
    private array $events = [];

    /**
     * @param callable[][] ...$listeners Callables that will handle events.
     */
    public function __construct(array $listeners)
    {
        $this->listeners = $listeners;
    }

    public function dispatch(object $event): object
    {
        $this->events[] = $event;
        foreach ($this->forEvent($event) as $listener) {
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

    public function isObjectTriggered(object $event): bool
    {
        return (in_array($event, $this->events, true));
    }

    public function isClassTriggered(string $class): bool
    {
        return $this->walkBool(static fn(object $event): bool => get_class($event) === $class);
    }

    public function isInstanceOfTriggered(string $class): bool
    {
        return $this->walkBool(static fn(object $event): bool => $event instanceof $class);
    }

    private function walkBool(Closure $closure)
    {
        foreach ($this->events as $event) {
            if ($closure($event)) {
                return true;
            }
        }
        return false;
    }

    private function forEvent(object $event): iterable
    {
        yield from $this->forClass(get_class($event));
        yield from $this->forClass(...array_values(class_parents($event)));
        yield from $this->forClass(...array_values(class_implements($event)));
    }

    private function forClass(string ...$classNames): iterable
    {
        foreach ($classNames as $className) {
            if (isset($this->listeners[$className])) {
                yield from $this->listeners[$className];
            }
        }
    }
}
