<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\PHPUnitAssertTrait;

final class PHPUnitAssertTraitTest extends TestCase
{
    use PHPUnitAssertTrait;

    public function dataAssertEqualStringsIgnoringLineEndings(): array
    {
        return [
            'lf-crlf' => ["a\nb", "a\r\nb"],
            'cr-crlf' => ["a\rb", "a\r\nb"],
            'crlf-crlf' => ["a\r\nb", "a\r\nb"],
            'lf-cr' => ["a\nb", "a\rb"],
            'cr-cr' => ["a\rb", "a\rb"],
            'crlf-cr' => ["a\r\nb", "a\rb"],
            'lf-lf' => ["a\nb", "a\nb"],
            'cr-lf' => ["a\rb", "a\nb"],
            'crlf-lf' => ["a\r\nb", "a\nb"],
        ];
    }

    /**
     * @dataProvider dataAssertEqualStringsIgnoringLineEndings
     */
    public function testAssertEqualStringsIgnoringLineEndings(string $expected, string $actual): void
    {
        $this->assertEqualStringsIgnoringLineEndings($expected, $actual);
    }

    public function dataNotAssertEqualStringsIgnoringLineEndings(): array
    {
        return [
            ["a\nb", 'ab'],
            ["a\rb", 'ab'],
            ["a\r\nb", 'ab'],
        ];
    }

    /**
     * @dataProvider dataNotAssertEqualStringsIgnoringLineEndings
     */
    public function testNotAssertEqualStringsIgnoringLineEndings(string $expected, string $actual): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->assertEqualStringsIgnoringLineEndings($expected, $actual);
    }

    public function dataAssertStringContainsStringIgnoringLineEndings(): array
    {
        return [
            ["b\nc", "b\r\nc"],
            ["b\nc", "a\r\nb\r\nc\r\nd"],
        ];
    }

    /**
     * @dataProvider dataAssertStringContainsStringIgnoringLineEndings
     */
    public function testAssertStringContainsStringIgnoringLineEndings(string $needle, string $haystack): void
    {
        $this->assertStringContainsStringIgnoringLineEndings($needle, $haystack);
    }

    public function testNotAssertStringContainsStringIgnoringLineEndings(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->assertStringContainsStringIgnoringLineEndings("b\nc", "\r\nc\r\n");
    }
}
