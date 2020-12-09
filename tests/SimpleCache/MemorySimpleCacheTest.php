<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;

final class MemorySimpleCacheTest extends BaseSimpleCacheTest
{
    protected function createCacheInstance(array $data = []): MemorySimpleCache
    {
        return new MemorySimpleCache($data);
    }

    public function testInitialData(): void
    {
        $data = ['foo' => 'bar', 'key' => 'value'];
        $cache = $this->createCacheInstance($data);

        $this->assertSame('bar', $cache->get('foo'));
        $this->assertSame('value', $cache->get('key'));
    }

    public function testGetValues(): void
    {
        $data = ['foo' => 'bar', 'key' => 'value'];
        $cache = $this->createCacheInstance($data);

        $this->assertSame($data, $cache->getValues());
    }
}
