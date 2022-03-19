<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

use function array_key_exists;

final class SimpleContainer implements ContainerInterface
{
    private array $definitions;

    /**
     * @psalm-var Closure(string)
     */
    private Closure $factory;

    /**
     * @psalm-var Closure(string): bool
     */
    private Closure $hasCallback;

    /**
     * @param array $definitions
     * @param Closure|null $factory Should be closure that works like ContainerInterface::get(string $id): mixed
     * @param Closure|null $hasCallback Should be closure that works like ContainerInterface::has(string $id): bool
     *
     * @psalm-param Closure(string) $factory
     * @psalm-param Closure(string):bool $hasCallback
     */
    public function __construct(
        array $definitions = [],
        ?Closure $factory = null,
        ?Closure $hasCallback = null
    ) {
        $this->definitions = $definitions;

        $this->factory = $factory ??
            /** @return mixed */
            static function (string $id) {
                throw new NotFoundException($id);
            };

        $this->hasCallback = $hasCallback ??
            static function (string $id): bool {
                return false;
            };
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
        return ($this->hasCallback)($id);
    }
}
