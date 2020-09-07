<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\EventDispatcher;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;

final class SimpleEventDispatcher implements EventDispatcherInterface
{
    private ?Closure $dispatcher;
    /** @var object[] */
    private array $events = [];

    /**
     * @param null|Closure $dispatcher Function that will handle each event.
     * This function must return an object.
     */
    public function __construct(Closure $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(object $event): object
    {
        $this->events[] = $event;
        return $this->dispatcher === null ? $event : ($this->dispatcher)($event);
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
        return $this->walkBool(static fn (object $event): bool => get_class($event) === $class);
    }

    public function isInstanceOfTriggered(string $class): bool
    {
        return $this->walkBool(static fn (object $event): bool => $event instanceof $class);
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
}
