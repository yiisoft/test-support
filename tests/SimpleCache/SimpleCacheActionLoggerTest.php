<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Test\Support\SimpleCache\Action;
use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;
use Yiisoft\Test\Support\SimpleCache\SimpleCacheActionLogger;

final class SimpleCacheActionLoggerTest extends BaseSimpleCacheTest
{
    protected function createCacheInstance(array $data = []): SimpleCacheActionLogger
    {
        return new SimpleCacheActionLogger(new MemorySimpleCache(), $data);
    }

    public function testGetCacheService(): void
    {
        $cache = $this->createCacheInstance();

        $this->assertInstanceOf(CacheInterface::class, $cache->getCacheService());
        $this->assertNotSame($cache, $cache->getCacheService());
    }

    public function testGetActions(): void
    {
        $cache = $this->createCacheInstance();
        $cache->setMultiple(['foo' => 'bar', 'key' => 'value']);

        $actions = $cache->getActions();

        $this->assertCount(2, $actions);
        $this->assertContainsOnlyInstancesOf(Action::class, $actions);
        $this->assertSame('foo', $actions[0]->getKey());
        $this->assertSame('key', $actions[1]->getKey());
    }

    public function testGetActionKeyList(): void
    {
        $cache = $this->createCacheInstance();
        $cache->setMultiple(['foo' => 'bar', 'key' => 'value']);
        $cache->delete('foo');

        $actions = $cache->getActionKeyList();

        $this->assertSame([
            [Action::SET, 'foo'],
            [Action::SET, 'key'],
            [Action::DELETE, 'foo'],
        ], $actions);
    }

    public function testInitialData(): void
    {
        $data = ['foo' => 'bar', 'key' => 'value'];
        $cache = $this->createCacheInstance($data);

        $this->assertSame('bar', $cache->get('foo'));
        $this->assertSame('value', $cache->get('key'));
    }
}
