<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

/**
 * @template-covariant TAction as string
 * @template-covariant TKey as mixed
 * @template-covariant TValue as mixed
 * @template-covariant TTtl as mixed
 */
final class Action
{
    public const GET = 'get';
    public const SET = 'set';
    public const DELETE = 'delete';
    public const CLEAR = 'clear';
    public const HAS = 'has';

    /**
     * @param TAction $action
     * @param TKey $key
     * @param TValue $value
     * @param TTtl $ttl
     */
    private function __construct(
        private string $action,
        private mixed $key = null,
        private mixed $value = null,
        private mixed $ttl = null,
    ) {}

    /**
     * @return TAction
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return TKey
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return TValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return TTtl
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return Action<'get', mixed, null, null>
     */
    public static function createGetAction(mixed $key): self
    {
        return new self(self::GET, $key);
    }

    /**
     * @return Action<'has', mixed, null, null>
     */
    public static function createHasAction(mixed $key): self
    {
        return new self(self::HAS, $key);
    }

    /**
     * @return Action<'set', mixed, mixed, mixed>
     */
    public static function createSetAction(mixed $key, mixed $value, mixed $ttl): self
    {
        return new self(self::SET, $key, $value, $ttl);
    }

    /**
     * @return Action<'delete', mixed, null, null>
     */
    public static function createDeleteAction(mixed $key): self
    {
        return new self(self::DELETE, $key);
    }

    /**
     * @return Action<'clear', null, null, null>
     */
    public static function createClearAction(): self
    {
        return new self(self::CLEAR);
    }
}
