<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Log;

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use stdClass;
use Yiisoft\Test\Support\Log\SimpleLogger;

final class SimpleLoggerTest extends TestCase
{
    private SimpleLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new SimpleLogger();
    }

    public function testLog(): void
    {
        $this->logger->log(LogLevel::INFO, 'test1');
        $messages = $this->logger->getMessages();

        $this->assertCount(1, $messages);
        $this->assertSame(LogLevel::INFO, $messages[0]['level']);
        $this->assertSame('test1', $messages[0]['message']);
        $this->assertSame([], $messages[0]['context']);

        $this->logger->log(LogLevel::ERROR, 'test2', ['category' => 'app']);
        $messages = $this->logger->getMessages();

        $this->assertCount(2, $messages);
        $this->assertSame(LogLevel::ERROR, $messages[1]['level']);
        $this->assertSame('test2', $messages[1]['message']);
        $this->assertSame(['category' => 'app'], $messages[1]['context']);
    }

    public function testLogWithStringableMessage(): void
    {
        $message = new RuntimeException('Some error');
        $this->logger->log(LogLevel::ERROR, $message);
        $messages = $this->logger->getMessages();

        $this->assertNotEmpty($messages);
    }

    public function invalidMessageLevelProvider(): array
    {
        return [
            'string' => ['unknown'],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'null' => [null],
            'array' => [[]],
            'closure' => [fn () => null],
            'object' => [new stdClass()],
        ];
    }

    /**
     * @dataProvider invalidMessageLevelProvider
     */
    public function testGetLevelNameThrowExceptionForInvalidMessageLevel(mixed $level): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->log($level, 'message');
    }

    public function parseMessageProvider(): array
    {
        return [
            [
                'no placeholder',
                ['foo' => 'some'],
                'no placeholder',
            ],
            [
                'has {foo} placeholder',
                ['foo' => 'some'],
                'has some placeholder',
            ],
            [
                'has {foo} placeholder',
                [],
                'has {foo} placeholder',
            ],
        ];
    }

    /**
     * @dataProvider parseMessageProvider
     */
    public function testParseMessage(string $message, array $context, string $expected): void
    {
        $this->logger->info($message, $context);
        $messages = $this->logger->getMessages();
        $this->assertSame($expected, $messages[0]['message']);
    }
}
