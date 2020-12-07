<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use Yiisoft\Test\Support\SimpleCache\ExtendedSimpleCache;

class ExtendedSimpleCacheTest extends MemorySimpleCacheTest
{
    protected function createCacheInstance(array $data = []): ExtendedSimpleCache
    {
        return new ExtendedSimpleCache($data);
    }

    public function testInitialData(): void
    {
        $data = ['key' => 'value'];
        $cache = $this->createCacheInstance($data);

        $this->assertSame($data, $cache->getValues());
    }
}
