<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

use function array_key_exists;

final class SimpleContainer implements ContainerInterface
{
    /**
     * @psalm-var Closure(string)
     */
    private Closure $factory;

    /**
     * @psalm-var Closure(string): bool
     */
    private Closure $hasCallback;

    /**
     * @param Closure|null $factory Should be closure that works like ContainerInterface::get(string $id): mixed
     * @param Closure|null $hasCallback Should be closure that works like ContainerInterface::has(string $id): bool
     *
     * @psalm-param Closure(string) $factory
     * @psalm-param Closure(string):bool $hasCallback
     */
    public function __construct(
        private array $definitions = [],
        ?Closure $factory = null,
        ?Closure $hasCallback = null
    ) {
        $this->factory = $factory ??
            /** @return mixed */
            static function (string $id) {
                throw new NotFoundException($id);
            };

        $this->hasCallback = $hasCallback ??
            function (string $id): bool {
                try {
                    $this->get($id);
                    return true;
                } catch (NotFoundException) {
                    return false;
                }
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
