<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TypeError;

abstract class BaseContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertSame('bar', $container->get('foo'));
    }

    public function testGetNotFound(): void
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->expectException(NotFoundExceptionInterface::class);

        $container->get('baz');
    }

    public function testHasYes(): void
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertTrue($container->has('foo'));
    }

    public function testHasNo(): void
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertFalse($container->has('baz'));
    }

    public function testHasNullValue(): void
    {
        $container = $this->createContainer(['foo' => null]);

        $this->assertTrue($container->has('foo'));
    }

    public function testHasParameterType(): void
    {
        $this->expectException(TypeError::class);

        $this->createContainer()->has(1);
    }

    public function testGetParameterType(): void
    {
        $this->expectException(TypeError::class);

        $this->createContainer()->get(1);
    }

    abstract protected function createContainer(array $definitions = []): ContainerInterface;
}
