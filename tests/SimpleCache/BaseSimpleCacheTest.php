<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use ArrayIterator;
use DateInterval;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

use function is_object;

abstract class BaseSimpleCacheTest extends TestCase
{
    abstract protected function createCacheInstance(): CacheInterface;

    /**
     * @dataProvider dataProvider
     */
    public function testSet($key, $value): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($cache->set($key, $value));
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet($key, $value): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->set($key, $value);
        $valueFromCache = $cache->get($key, 'default');

        $this->assertEquals($value, $valueFromCache);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValueInCacheCannotBeChanged($key, $value): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->set($key, $value);
        $valueFromCache = $cache->get($key, 'default');

        $this->assertEquals($value, $valueFromCache);

        if (is_object($value)) {
            $originalValue = clone $value;
            $valueFromCache->test_field = 'changed';
            $value->test_field = 'changed';
            $valueFromCacheNew = $cache->get($key, 'default');
            $this->assertEquals($originalValue, $valueFromCacheNew);
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($key, $value): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->set($key, $value);

        $this->assertTrue($cache->has($key));
        // check whether exists affects the value
        $this->assertEquals($value, $cache->get($key));

        $this->assertTrue($cache->has($key));
        $this->assertFalse($cache->has('not_exists'));
    }

    public function testGetNonExistent(): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $this->assertNull($cache->get('non_existent_key'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDelete($key, $value): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->set($key, $value);

        $this->assertEquals($value, $cache->get($key));
        $this->assertTrue($cache->delete($key));
        $this->assertNull($cache->get($key));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testClear(bool|string|int|float $key, mixed $value): void
    {
        $cache = $this->createCacheInstance();
        $this->prepare($cache);

        $this->assertTrue($cache->clear());
        $this->assertNull($cache->get($key));
    }

    /**
     * @dataProvider dataProviderSetMultiple
     */
    public function testSetMultiple(?int $ttl): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $data = $this->getDataProviderData();

        $result = $cache->setMultiple($data, $ttl);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $cache->get((string)$key));
        }
        $this->assertTrue($result);
    }

    /**
     * @return array testing multiSet with and without expiry
     */
    public function dataProviderSetMultiple(): array
    {
        return [
            [null],
            [2],
        ];
    }

    public function testGetMultiple(): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));

        $cache->setMultiple($data);

        $this->assertEquals($data, $cache->getMultiple($keys));
    }

    public function testDeleteMultiple(): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));

        $cache->setMultiple($data);

        $this->assertEquals($data, $cache->getMultiple($keys));

        $result = $cache->deleteMultiple($keys);

        $emptyData = array_map(static fn ($v) => null, $data);

        $this->assertSame($emptyData, $cache->getMultiple($keys));
        $this->assertTrue($result);
    }

    public function testZeroAndNegativeTtl()
    {
        $cache = $this->createCacheInstance();
        $cache->clear();
        $cache->setMultiple([
            'a' => 1,
            'b' => 2,
        ]);

        $this->assertTrue($cache->has('a'));
        $this->assertTrue($cache->has('b'));

        $cache->set('a', 11, -1);

        $this->assertFalse($cache->has('a'));

        $cache->set('b', 22, 0);

        $this->assertFalse($cache->has('b'));
    }

    /**
     * @dataProvider iterableProvider
     */
    public function testValuesAsIterable(array $array, iterable $iterable): void
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->setMultiple($iterable);

        $this->assertSame($array, $cache->getMultiple(array_keys($array)));
    }

    public function iterableProvider(): array
    {
        return [
            'array' => [
                ['a' => 1, 'b' => 2,],
                ['a' => 1, 'b' => 2,],
            ],
            \ArrayIterator::class => [
                ['a' => 1, 'b' => 2,],
                new ArrayIterator(['a' => 1, 'b' => 2,]),
            ],
            \IteratorAggregate::class => [
                ['a' => 1, 'b' => 2,],
                new class () implements IteratorAggregate {
                    public function getIterator(): ArrayIterator
                    {
                        return new ArrayIterator(['a' => 1, 'b' => 2,]);
                    }
                },
            ],
            'generator' => [
                ['a' => 1, 'b' => 2,],
                (static function () {
                    yield 'a' => 1;
                    yield 'b' => 2;
                })(),
            ],
        ];
    }

    public function testSetWithDateIntervalTtl()
    {
        $cache = $this->createCacheInstance();
        $cache->clear();

        $cache->set('a', 1, new DateInterval('PT1H'));
        $this->assertSame(1, $cache->get('a'));

        $cache->setMultiple(['b' => 2]);
        $this->assertSame(['b' => 2], $cache->getMultiple(['b']));
    }

    public function dataProvider(): array
    {
        $object = new stdClass();
        $object->test_field = 'test_value';
        return [
            'integer' => ['test_integer', 1],
            'double' => ['test_double', 1.1],
            'string' => ['test_string', 'a'],
            'boolean_true' => ['test_boolean_true', true],
            'boolean_false' => ['test_boolean_false', false],
            'object' => ['test_object', $object],
            'array' => ['test_array', ['test_key' => 'test_value']],
            'null' => ['test_null', null],
            'supported_key_characters' => ['AZaz09_.', 'b'],
            '64_characters_key_max' => ['bVGEIeslJXtDPrtK.hgo6HL25_.1BGmzo4VA25YKHveHh7v9tUP8r5BNCyLhx4zy', 'c'],
            'string_with_number_key' => ['111', 11],
            'string_with_number_key_1' => ['022', 22],
        ];
    }

    public function incorrectKeyProvider(): array
    {
        return [
            [''],
            ['{key}'],
            ['(key)'],
            ['root/child'],
            ['root\\child'],
            ['group@item'],
            ['group:item'],
        ];
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testSetMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        $cache->set($key, 'value');
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testGetMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        $cache->get($key);
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testHasMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        $cache->has($key);
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testDeleteMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        $cache->delete($key);
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testSetMultipleMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        try {
            $cache->setMultiple(['key' => 'normal-value', $key => 'value']);
        } finally {
            $this->assertFalse($cache->has('key'));
        }
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testGetMultipleMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();

        $this->expectException(InvalidArgumentException::class);

        $cache->getMultiple([$key]);
    }

    /**
     * @dataProvider incorrectKeyProvider
     */
    public function testDeleteMultipleMethodKeyChecking(mixed $key): void
    {
        $cache = $this->createCacheInstance();
        $cache->set('normal-key', 'normal-value');

        $this->expectException(InvalidArgumentException::class);

        try {
            $cache->deleteMultiple(['normal-key', $key, 'normal-key']);
        } finally {
            $this->assertTrue($cache->has('normal-key'));
        }
    }

    private function getDataProviderData($keyPrefix = ''): array
    {
        $dataProvider = $this->dataProvider();
        $data = [];
        foreach ($dataProvider as $item) {
            $data[$keyPrefix . $item[0]] = $item[1];
        }

        return $data;
    }

    /**
     * This function configures given cache to match some expectations
     */
    private function prepare(CacheInterface $cache): void
    {
        $cache->clear();

        $data = $this->dataProvider();

        foreach ($data as $datum) {
            $cache->set($datum[0], $datum[1]);
        }
    }
}
