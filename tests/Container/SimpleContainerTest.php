<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class SimpleContainerTest extends BaseContainerTest
{
    public function testGetFromCustomClosure(): void
    {
        $container = $this->createContainer([], static fn ($id) => $id);

        $this->assertSame('foo', $container->get('foo'));
    }

    public function testHasFromCustomClosure(): void
    {
        $container = $this->createContainer([], static fn ($id) => $id);

        $this->assertTrue($container->has('foo'));
    }

    public function testLowClosurePriority(): void
    {
        $container = $this->createContainer(['foo' => 'foo'], static fn ($id) => 'bar');

        $this->assertSame('foo', $container->get('foo'));
    }

    public function testClosureWithContainerInterface(): void
    {
        $container = $this->createContainer([], static fn (string $id, SimpleContainer $container) => $container);

        $this->assertSame($container, $container->get(ContainerInterface::class));
    }

    protected function createContainer(array $definitions = [], Closure $closure = null): SimpleContainer
    {
        return new SimpleContainer($definitions, $closure);
    }
}
