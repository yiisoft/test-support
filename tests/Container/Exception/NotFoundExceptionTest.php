<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container\Exception;

use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;

final class NotFoundExceptionTest extends TestCase
{
    private const DEFAULT_ID = 'Default id';

    public function testInitialState()
    {
        $exception = new NotFoundException(self::DEFAULT_ID);

        $this->assertSame(0, $exception->getCode());
        $this->assertSame('No definition or class found for "' . self::DEFAULT_ID . '".', $exception->getMessage());
        $this->assertNull($exception->getPrevious());
    }

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
