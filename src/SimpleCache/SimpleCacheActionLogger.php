<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Traversable;
use Yiisoft\Test\Support\SimpleCache\Exception\InvalidArgumentException;

/**
 * @template TCacheService as CacheInterface
 */
final class SimpleCacheActionLogger implements CacheInterface
{
    /** @var Action[] */
    private array $actions = [];
    /** @psalm-var TCacheService */
    private CacheInterface $cacheService;

    /**
     * SimpleCacheActionLogger constructor.
     *
     * @param array $cacheData
     * @param CacheInterface $cacheService
     * @psalm-param TCacheService $cacheService
     */
    public function __construct(CacheInterface $cacheService, array $cacheData = [])
    {
        $this->cacheService = $cacheService;
        $this->cacheService->setMultiple($cacheData);
        $this->actions = [];
    }

    public function get($key, $default = null)
    {
        $this->actions[] = Action::createGetAction($key);
        return $this->cacheService->get($key, $default);
    }

    public function delete($key): bool
    {
        $this->actions[] = Action::createDeleteAction($key);
        return $this->cacheService->delete($key);
    }

    public function has($key): bool
    {
        $this->actions[] = Action::createHasAction($key);
        return $this->cacheService->has($key);
    }

    public function clear(): bool
    {
        $this->actions[] = Action::createClearAction();
        return $this->cacheService->clear();
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->actions[] = Action::createSetAction($key, $value, $ttl);
        return $this->cacheService->set($key, $value, $ttl);
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        /** @psalm-var mixed $key */
        foreach ($keys as $key) {
            $this->actions[] = Action::createGetAction($key);
        }
        return $this->cacheService->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        /** @psalm-var mixed $value */
        foreach ($values as $key => $value) {
            $this->actions[] = Action::createSetAction($key, $value, $ttl);
        }
        return $this->cacheService->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $keys = $this->iterableToArray($keys);
        /** @psalm-var mixed $key */
        foreach ($keys as $key) {
            $this->actions[] = Action::createDeleteAction($key);
        }
        return $this->cacheService->deleteMultiple($keys);
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return array<int, array{0: string, 1: mixed}>
     */
    public function getShortActions(): array
    {
        $result = [];
        foreach ($this->actions as $action) {
            $result[] = [$action->getAction(), $action->getKey()];
        }
        return $result;
    }

    /**
     * @return CacheInterface
     * @psalm-return TCacheService
     */
    public function getCacheService(): CacheInterface
    {
        return $this->cacheService;
    }

    /**
     * @param mixed $iterable
     *
     * Converts iterable to array. If provided value is not iterable it throws an InvalidArgumentException
     */
    private function iterableToArray($iterable): array
    {
        if (!is_iterable($iterable)) {
            throw new InvalidArgumentException(sprintf("Iterable is expected, got %s.", gettype($iterable)));
        }
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : $iterable;
    }
}
