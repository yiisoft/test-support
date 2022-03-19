<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class SimpleContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = new SimpleContainer(['foo' => 'bar']);

        $this->assertSame('bar', $container->get('foo'));
    }

    public function testGetNotFound(): void
    {
        $container = new SimpleContainer(['foo' => 'bar']);

        $this->expectException(NotFoundExceptionInterface::class);

        $container->get('baz');
    }

    public function testHasYes(): void
    {
        $container = new SimpleContainer(['foo' => 'bar']);

        $this->assertTrue($container->has('foo'));
    }

    public function testHasNo(): void
    {
        $container = new SimpleContainer(['foo' => 'bar']);

        $this->assertFalse($container->has('baz'));
    }

    public function testHasNullValue(): void
    {
        $container = new SimpleContainer(['foo' => null]);

        $this->assertTrue($container->has('foo'));
    }

    public function testGetFromCustomClosure(): void
    {
        $container = new SimpleContainer(
            [],
            static fn (string $id) => $id
        );

        $this->assertSame('foo', $container->get('foo'));
    }

    public function testHasFromCustomClosure(): void
    {
        $container = new SimpleContainer(
            [],
            static fn (string $id) => $id,
            static fn (string $id): bool => true,
        );

        $this->assertTrue($container->has('foo'));
    }

    public function testLowClosurePriority(): void
    {
        $container = new SimpleContainer(
            ['foo' => 'foo'],
            static fn (string $id) => 'bar'
        );

        $this->assertSame('foo', $container->get('foo'));
    }
}
