<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

final class ExtendedSimpleCache extends MemorySimpleCache
{
    /** @var Action[] */
    private array $actions = [];

    public function __construct(array $cacheData = [])
    {
        parent::__construct($cacheData);
        $this->actions = [];
    }

    public function get($key, $default = null)
    {
        $this->actions[] = Action::createGetAction($key);
        return parent::get($key, $default);
    }

    public function delete($key): bool
    {
        $this->actions[] = Action::createDeleteAction($key);
        return parent::delete($key);
    }

    public function has($key): bool
    {
        $this->actions[] = Action::createHasAction($key);
        return parent::has($key);
    }

    public function clear(): bool
    {
        $this->actions[] = Action::createClearAction();
        return parent::clear();
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->actions[] = Action::createSetAction($key, $value, $ttl);
        return parent::set($key, $value, $ttl);
    }

    /**
     * Get stored data
     *
     * @return array<array-key, mixed>
     */
    public function getValues(): array
    {
        $result = [];
        foreach ($this->cache as $key => $value) {
            $result[$key] = $value[0];
        }
        return $result;
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
}
