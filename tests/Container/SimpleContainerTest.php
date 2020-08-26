<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use Yiisoft\Test\Support\Container\SimpleContainer;

final class SimpleContainerTest extends BaseContainerTest
{
    protected function createContainer(array $definitions = []): SimpleContainer
    {
        return new SimpleContainer($definitions);
    }
}
