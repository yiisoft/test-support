<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\EventDispatcher\Stub;

use Psr\EventDispatcher\StoppableEventInterface;

final class StoppableEvent implements StoppableEventInterface
{
    public function __construct(private bool $propagationStopped = false)
    {
    }

    public function setPropagationStopped(bool $value): void
    {
        $this->propagationStopped = $value;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
