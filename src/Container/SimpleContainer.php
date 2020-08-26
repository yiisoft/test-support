<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

final class SimpleContainer implements ContainerInterface
{
    private array $definitions;

    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException($id);
        }
        return $this->definitions[$id];
    }

    public function has($id)
    {
        return array_key_exists($id, $this->definitions);
    }
}
