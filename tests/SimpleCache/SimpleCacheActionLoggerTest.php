<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;
use Yiisoft\Test\Support\SimpleCache\SimpleCacheActionLogger;

final class SimpleCacheActionLoggerTest extends BaseSimpleCacheTest
{
    protected function createCacheInstance(array $data = []): SimpleCacheActionLogger
    {
        return new SimpleCacheActionLogger(new MemorySimpleCache(), $data);
    }

    public function testInitialData(): void
    {
        $data = ['foo' => 'bar', 'key' => 'value'];
        $cache = $this->createCacheInstance($data);

        $this->assertSame('bar', $cache->get('foo'));
        $this->assertSame('value', $cache->get('key'));
    }
}
