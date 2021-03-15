<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Log;

use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use stdClass;
use Yiisoft\Test\Support\Log\SimpleLogger;

use function fclose;
use function fopen;

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

    public function testLogWithThrowableMessage(): void
    {
        $message = new RuntimeException('Some error');
        $this->logger->log(LogLevel::ERROR, $message);
        $messages = $this->logger->getMessages();

        $this->assertInstanceOf(RuntimeException::class, $messages[0]['context']['exception']);
        $this->assertSame($message, $messages[0]['context']['exception']);
    }

    public function messageProvider(): array
    {
        return [
            'string' => ['test', 'test'],
            'int' => [1, '1'],
            'float' => [1.1, '1.1'],
            'null' => [null, 'null'],
            'bool-true' => [true, 'true'],
            'bool-false' => [false, 'false'],
            'closure' => [fn () => 1, Closure::class],
            'object' => [new stdClass(), stdClass::class],
            'stringable-object' => [
                $stringableObject = new class() {
                    public function __toString(): string
                    {
                        return 'Stringable object';
                    }
                },
                $stringableObject->__toString(),
            ],
            'empty-array' => [[], '[]'],
            'callable-array' => [[$this, 'messageProvider'], '[{},"messageProvider"]'],
            'array' => [
                $array = [
                    'string' => 'string',
                    'int' => 1,
                    'float' => 1.1,
                    'bool' => true,
                    'null' => null,
                    'closure' => fn () => 1,
                    'object' => new stdClass(),
                    'stringable-object' => $stringableObject,
                    'empty-array' => [],
                    'callable-array' => [[$this, 'messageProvider'], '[{},"messageProvider"]'],
                    'nested-array' => [
                        'string' => 'string',
                        'int' => 1,
                        'float' => 1.1,
                        'bool' => true,
                        'null' => null,
                        'closure' => fn () => 1,
                        'object' => new stdClass(),
                        'stringable-object' => $stringableObject,
                        'empty-array' => [],
                        'callable-array' => [[$this, 'messageProvider'], '[{},"messageProvider"]'],
                    ],
                ],
                json_encode($array),
            ],
        ];
    }

    /**
     * @dataProvider messageProvider
     *
     * @param mixed $message
     * @param string $expected
     */
    public function testPsrLogInterfaceMethods($message, string $expected): void
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];
        $this->logger->emergency($message);
        $this->logger->alert($message);
        $this->logger->critical($message);
        $this->logger->error($message);
        $this->logger->warning($message);
        $this->logger->notice($message);
        $this->logger->info($message);
        $this->logger->debug($message);
        $this->logger->log(LogLevel::INFO, $message);

        $messages = $this->logger->getMessages();

        for ($i = 0, $levelsCount = count($levels); $i < $levelsCount; $i++) {
            $this->assertSame($levels[$i], $messages[$i]['level']);
            $this->assertSame($expected, $messages[$i]['message']);
        }

        $this->assertSame(LogLevel::INFO, $messages[8]['level']);
        $this->assertSame($expected, $messages[8]['message']);
    }

    public function testLogWithResourceMessage(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->logger->info($resource);
        $messages = $this->logger->getMessages();

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Resource id #', $messages[0]['message']);

        $this->logger->info([$resource]);
        $messages = $this->logger->getMessages();

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('[]', $messages[1]['message']);
        fclose($resource);
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
     *
     * @param mixed $level
     */
    public function testGetLevelNameThrowExceptionForInvalidMessageLevel($level): void
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
     *
     * @param string $message
     * @param array $context
     * @param string $expected
     */
    public function testParseMessage(string $message, array $context, string $expected): void
    {
        $this->logger->info($message, $context);
        $messages = $this->logger->getMessages();
        $this->assertSame($expected, $messages[0]['message']);
    }
}
