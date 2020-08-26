<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use PHPUnit\Framework\TestCase;

abstract class BaseContainerTest extends TestCase
{
    public function testGet()
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertSame('bar', $container->get('foo'));
    }
    public function testGetNotFound()
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->expectException(NotFoundExceptionInterface::class);

        $container->get('baz');
    }

    public function testHasYes()
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertTrue($container->has('foo'));
    }
    public function testHasNo()
    {
        $container = $this->createContainer(['foo' => 'bar']);

        $this->assertFalse($container->has('baz'));
    }
    public function testHasNullValue()
    {
        $container = $this->createContainer(['foo' => null]);

        $this->assertTrue($container->has('foo'));
    }

    abstract protected function createContainer(array $definitions = []): ContainerInterface;
}
