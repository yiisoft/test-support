<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Traversable;
use Yiisoft\Test\Support\SimpleCache\Exception\InvalidArgumentException;

use function array_key_exists;
use function is_object;
use function is_string;

final class MemorySimpleCache implements CacheInterface
{
    protected const EXPIRATION_INFINITY = 0;
    protected const EXPIRATION_EXPIRED = -1;

    public bool $returnOnSet = true;
    public bool $returnOnDelete = true;
    public bool $returnOnClear = true;

    /** @var array<string, array<int, mixed>> */
    protected array $cache = [];

    public function __construct(array $cacheData = [])
    {
        $this->setMultiple($cacheData);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        if (array_key_exists($key, $this->cache) && !$this->isExpired($key)) {
            $value = $this->cache[$key][0];
            if (is_object($value)) {
                $value = clone $value;
            }

            return $value;
        }

        return $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $expiration = $this->ttlToExpiration($ttl);
        if ($expiration < 0) {
            return $this->delete($key);
        }
        if (is_object($value)) {
            $value = clone $value;
        }
        $this->cache[$key] = [$value, $expiration];
        return $this->returnOnSet;
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        unset($this->cache[$key]);
        return $this->returnOnDelete;
    }

    public function clear(): bool
    {
        $this->cache = [];
        return $this->returnOnClear;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        /** @psalm-var string[] $keys */
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        $this->validateKeysOfValues($values);
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }
        return $this->returnOnSet;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        /** @var string[] $keys */
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return $this->returnOnDelete;
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);
        /** @psalm-var string $key */
        return isset($this->cache[$key]) && !$this->isExpired($key);
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

    /**
     * Checks whether item is expired or not
     */
    private function isExpired(string $key): bool
    {
        return $this->cache[$key][1] !== 0 && $this->cache[$key][1] <= time();
    }

    /**
     * Converts TTL to expiration.
     */
    private function ttlToExpiration(null|int|DateInterval $ttl): int
    {
        $ttl = $this->normalizeTtl($ttl);

        if ($ttl === null) {
            $expiration = self::EXPIRATION_INFINITY;
        } elseif ($ttl <= 0) {
            $expiration = self::EXPIRATION_EXPIRED;
        } else {
            $expiration = $ttl + time();
        }

        return $expiration;
    }

    /**
     * Normalizes cache TTL handling strings and {@see DateInterval} objects.
     *
     * @param DateInterval|int|null $ttl Raw TTL.
     *
     * @return int|null TTL value as UNIX timestamp or null meaning infinity.
     */
    private function normalizeTtl(null|int|DateInterval $ttl): ?int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))
                ->add($ttl)
                ->getTimestamp();
        }

        return $ttl;
    }

    /**
     * Converts iterable to array.
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : $iterable;
    }

    private function validateKey(mixed $key): void
    {
        if (!is_string($key) || $key === '' || strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException('Invalid key value.');
        }
    }

    /**
     * @param mixed[] $keys
     */
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    private function validateKeysOfValues(array $values): void
    {
        $keys = array_map('strval', array_keys($values));
        $this->validateKeys($keys);
    }
}
