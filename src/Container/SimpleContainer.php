<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Throwable;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use function array_key_exists;

final class SimpleContainer implements ContainerInterface
{
    private array $definitions;
    private Closure $factory;

    /**
     * @param array $definitions
     * @param Closure|null $factory Should be closure that works like ContainerInterface::get(string $id): mixed
     */
    public function __construct(array $definitions = [], Closure $factory = null)
    {
        $this->definitions = $definitions;
        $this->factory = $factory ?? static function (string $id): void {
            throw new NotFoundException($id);
        };
    }

    public function get($id)
    {
        $this->checkIdType($id);
        if (!array_key_exists($id, $this->definitions)) {
            $this->definitions[$id] = ($this->factory)($id);
        }
        return $this->definitions[$id];
    }

    public function has($id): bool
    {
        $this->checkIdType($id);
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }
        try {
            $this->get($id);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * This method added only for best compatibility with behavior of psr/container >=1.1
     */
    private function checkIdType(string $id): void
    {
    }
}
