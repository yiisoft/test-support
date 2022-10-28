<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * @template TCacheService as CacheInterface
 */
final class SimpleCacheActionLogger implements CacheInterface
{
    /** @var Action[] */
    private array $actions = [];

    /**
     * `SimpleCacheActionLogger` constructor.
     *
     * @param TCacheService $cacheService
     */
    public function __construct(private CacheInterface $cacheService, array $cacheData = [])
    {
        $this->cacheService->setMultiple($cacheData);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->actions[] = Action::createGetAction($key);
        return $this->cacheService->get($key, $default);
    }

    public function delete(string $key): bool
    {
        $this->actions[] = Action::createDeleteAction($key);
        return $this->cacheService->delete($key);
    }

    public function has(string $key): bool
    {
        $this->actions[] = Action::createHasAction($key);
        return $this->cacheService->has($key);
    }

    public function clear(): bool
    {
        $this->actions[] = Action::createClearAction();
        return $this->cacheService->clear();
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->actions[] = Action::createSetAction($key, $value, $ttl);
        return $this->cacheService->set($key, $value, $ttl);
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        /** @var mixed $key */
        foreach ($keys as $key) {
            $this->actions[] = Action::createGetAction($key);
        }
        return $this->cacheService->getMultiple($keys, $default);
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        /** @psalm-var mixed $value */
        foreach ($values as $key => $value) {
            $this->actions[] = Action::createSetAction($key, $value, $ttl);
        }
        return $this->cacheService->setMultiple($values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->iterableToArray($keys);
        /** @var mixed $key */
        foreach ($keys as $key) {
            $this->actions[] = Action::createDeleteAction($key);
        }
        return $this->cacheService->deleteMultiple($keys);
    }

    /**
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return array<int, array{0: string, 1: mixed}>
     */
    public function getActionKeyList(): array
    {
        $result = [];
        foreach ($this->actions as $action) {
            $result[] = [$action->getAction(), $action->getKey()];
        }
        return $result;
    }

    /**
     * @return TCacheService
     */
    public function getCacheService(): CacheInterface
    {
        return $this->cacheService;
    }

    /**
     * Converts iterable to array.
     *
     * @psalm-template T
     * @psalm-param iterable<T> $iterable
     * @psalm-return array<array-key,T>
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : $iterable;
    }
}
