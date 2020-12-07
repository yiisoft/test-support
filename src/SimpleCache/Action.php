<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache;

final class Action
{
    public const GET = 'get';
    public const SET = 'set';
    public const DELETE = 'delete';
    public const CLEAR = 'clear';
    public const HAS = 'has';

    private string $action;
    /** @var mixed */
    private $key;
    /** @var mixed */
    private $value;
    /** @var mixed */
    private $ttl;

    /**
     * @param string $action
     * @param mixed $key
     * @param mixed $value
     * @param mixed $ttl
     */
    private function __construct(string $action, $key = null, $value = null, $ttl = null)
    {
        $this->action = $action;
        $this->key = $key;
        $this->value = $value;
        $this->ttl = $ttl;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
     * @param $key
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
