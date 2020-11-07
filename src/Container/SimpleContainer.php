<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

final class SimpleContainer implements ContainerInterface
{
    private array $definitions;
    private Closure $factory;

    /**
     * @param array $definitions
     * @param null|Closure $factory Should be closure that works like ContainerInterface::get(string $id): mixed
     */
    public function __construct(array $definitions = [], Closure $factory = null)
    {
        $this->definitions = $definitions;
        $this->factory = $factory ?? static function (string $id): void { throw new NotFoundException($id); };
    }

    public function get($id)
    {
        if (!array_key_exists($id, $this->definitions)) {
            $this->definitions[$id] = ($this->factory)($id);
        }
        return $this->definitions[$id];
    }

    public function has($id): bool
    {
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }
        try {
            $this->get($id);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
