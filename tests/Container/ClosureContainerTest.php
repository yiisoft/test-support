<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use Closure;
use Yiisoft\Test\Support\Container\ClosureContainer;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

final class ClosureContainerTest extends BaseContainerTest
{
    public function testCustomClosure(): void
    {
        $container = $this->createContainer([], static fn ($id) => $id);

        $this->assertSame('foo', $container->get('foo'));
    }

    public function testLowClosurePriority(): void
    {
        $container = $this->createContainer(['foo' => 'foo'], static fn ($id) => 'bar');

        $this->assertSame('foo', $container->get('foo'));
    }

    protected function createContainer(array $definitions = [], Closure $closure = null): ClosureContainer
    {
        $closure = $closure ?? static function (string $id) { throw new NotFoundException($id); };
        return new ClosureContainer($closure, $definitions);
    }
}
