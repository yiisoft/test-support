<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

abstract class BaseSimpleCacheTest extends TestCase
{
    abstract protected function createCacheInstance(): CacheInterface;

    // public function testExpire(): void
    // {
    //     $cache = $this->createCacheInstance();
    //     $cache->clear();
    //
    //     MockHelper::$mock_time = \time();
    //     $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
    //
    //     MockHelper::$mock_time++;
    //     $this->assertTrue($cache->has('expire_test'));
    //     $this->assertSameExceptObject('expire_test', $cache->get('expire_test'));
    //
    //     MockHelper::$mock_time++;
    //     $this->assertFalse($cache->has('expire_test'));
    //     $this->assertNull($cache->get('expire_test'));
    // }

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
     *
     * @param scalar $key
     * @param mixed $value
     */
    public function testClear($key, $value): void
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

        $cache->setMultiple($data, $ttl);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $cache->get((string)$key));
        }
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

        $cache->deleteMultiple($keys);

        $emptyData = array_map(static function ($v) {
            return null;
        }, $data);

        $this->assertSame($emptyData, $cache->getMultiple($keys));
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

    // /**
    //  * @dataProvider dataProviderNormalizeTtl
    //  */
    // public function testNormalizeTtl($ttl, $expectedResult): void
    // {
    //     $cache = new ExtendedSimpleCache();
    //     $this->assertSameExceptObject($expectedResult, $this->invokeMethod($cache, 'normalizeTtl', [$ttl]));
    // }
    //
    // public function dataProviderNormalizeTtl(): array
    // {
    //     return [
    //         [123, 123],
    //         ['123', 123],
    //         [null, null],
    //         [0, 0],
    //         [new DateInterval('PT6H8M'), 6 * 3600 + 8 * 60],
    //         [new DateInterval('P2Y4D'), 2 * 365 * 24 * 3600 + 4 * 24 * 3600],
    //     ];
    // }

    // /**
    //  * @dataProvider ttlToExpirationProvider
    //  *
    //  * @param mixed $ttl
    //  * @param mixed $expected
    //  *
    //  * @throws ReflectionException
    //  */
    // public function testTtlToExpiration($ttl, $expected): void
    // {
    //     if ($expected === 'calculate_expiration') {
    //         MockHelper::$mock_time = \time();
    //         $expected = MockHelper::$mock_time + $ttl;
    //     }
    //     $cache = new ExtendedSimpleCache();
    //     $this->assertSameExceptObject($expected, $this->invokeMethod($cache, 'ttlToExpiration', [$ttl]));
    // }
    //
    // public function ttlToExpirationProvider(): array
    // {
    //     return [
    //         [3, 'calculate_expiration'],
    //         [null, 0],
    //         [-5, -1],
    //     ];
    // }

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
            'ArrayIterator' => [
                ['a' => 1, 'b' => 2,],
                new \ArrayIterator(['a' => 1, 'b' => 2,]),
            ],
            'IteratorAggregate' => [
                ['a' => 1, 'b' => 2,],
                new class() implements \IteratorAggregate {
                    public function getIterator()
                    {
                        return new \ArrayIterator(['a' => 1, 'b' => 2,]);
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

    public function testGetInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->get(1);
    }

    public function testSetInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->set(1, 1);
    }

    public function testDeleteInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->delete(1);
    }

    public function testGetMultipleInvalidKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->getMultiple([true]);
    }

    public function testGetMultipleInvalidKeysNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->getMultiple(1);
    }

    public function testSetMultipleInvalidKeysNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->setMultiple(1);
    }

    public function testDeleteMultipleInvalidKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->deleteMultiple([true]);
    }

    public function testDeleteMultipleInvalidKeysNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->deleteMultiple(1);
    }

    public function testHasInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $cache = $this->createCacheInstance();
        $cache->has(1);
    }

    public function dataProvider(): array
    {
        $object = new \stdClass();
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
