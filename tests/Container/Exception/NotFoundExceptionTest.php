<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container\Exception;

use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class NotFoundExceptionTest extends TestCase
{
    private const DEFAULT_ID = 'Default id';

    public function testGetId()
    {
        $exception = $this->createException();

        $this->assertSame(self::DEFAULT_ID, $exception->getId());
    }

    private function createException(string $id = self::DEFAULT_ID): NotFoundException
    {
        return new NotFoundException($id);
    }
}
