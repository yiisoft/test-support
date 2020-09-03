<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Closure;
use Psr\Container\ContainerInterface;

final class ClosureContainer implements ContainerInterface
{
    private Closure $factory;
    private array $definitions;

    public function __construct(Closure $factory, array $definitions = [])
    {
        $this->factory = $factory;
        $this->definitions = $definitions;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            $this->definitions[$id] = ($this->factory)($id);
        }
        return $this->definitions[$id];
    }

    public function has($id)
    {
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }
        try {
            $this->get($id);
            return true;
        } catch (\Throwable $e) { // @phan-suppress-current-line PhanUnusedVariableCaughtException
            return false;
        }
    }
}
