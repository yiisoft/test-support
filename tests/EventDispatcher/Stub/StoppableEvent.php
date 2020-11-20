<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\EventDispatcher\Stub;

use Psr\EventDispatcher\StoppableEventInterface;

class StoppableEvent implements StoppableEventInterface
{
    private bool $propagationStopped;

    public function __construct(bool $propagationStopped = false)
    {
        $this->propagationStopped = $propagationStopped;
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
