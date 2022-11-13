<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

/**
 * @template TAction as string
 * @template TKey as mixed
 * @template TValue as mixed
 * @template TTtl as mixed
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
        private mixed $ttl = null
    ) {
    }

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

    public static function createGetAction(mixed $key): self
    {
        return new self(self::GET, $key);
    }

    public static function createHasAction(mixed $key): self
    {
        return new self(self::HAS, $key);
    }

    public static function createSetAction(mixed $key, mixed $value, mixed $ttl): self
    {
        return new self(self::SET, $key, $value, $ttl);
    }

    public static function createDeleteAction(mixed $key): self
    {
        return new self(self::DELETE, $key);
    }

    public static function createClearAction(): self
    {
        return new self(self::CLEAR);
    }
}
