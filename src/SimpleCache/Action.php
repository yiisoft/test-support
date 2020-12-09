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

    /** @var TAction  */
    private string $action;
    /** @var TKey */
    private $key;
    /** @var TValue */
    private $value;
    /** @var TTtl */
    private $ttl;

    /**
     * @param TAction $action
     * @param TKey $key
     * @param TValue $value
     * @param TTtl $ttl
     */
    private function __construct(string $action, $key = null, $value = null, $ttl = null)
    {
        $this->action = $action;
        $this->key = $key;
        $this->value = $value;
        $this->ttl = $ttl;
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

    /**
     * @param mixed $key
     */
    public static function createGetAction($key): self
    {
        return new self(self::GET, $key);
    }

    /**
     * @param mixed $key
     */
    public static function createHasAction($key): self
    {
        return new self(self::HAS, $key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param mixed $ttl
     */
    public static function createSetAction($key, $value, $ttl): self
    {
        return new self(self::SET, $key, $value, $ttl);
    }

    /**
     * @param mixed $key
     */
    public static function createDeleteAction($key): self
    {
        return new self(self::DELETE, $key);
    }

    public static function createClearAction(): self
    {
        return new self(self::CLEAR);
    }
}
