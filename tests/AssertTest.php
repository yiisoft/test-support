<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Assert;

final class AssertTest extends TestCase
{
    public function dataAssertEqualIgnoringLineEndings(): array
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
     * @dataProvider dataAssertEqualIgnoringLineEndings
     */
    public function testAssertEqualIgnoringLineEndings(string $expected, string $actual): void
    {
        Assert::assertEqualIgnoringLineEndings($expected, $actual);
    }

    public function dataNotAssertEqualIgnoringLineEndings(): array
    {
        return [
            ["a\nb", 'ab'],
            ["a\rb", 'ab'],
            ["a\r\nb", 'ab'],
        ];
    }

    /**
     * @dataProvider dataNotAssertEqualIgnoringLineEndings
     */
    public function testNotAssertEqualIgnoringLineEndings(string $expected, string $actual): void
    {
        $this->expectException(ExpectationFailedException::class);
        Assert::assertEqualIgnoringLineEndings($expected, $actual);
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
        Assert::assertStringContainsStringIgnoringLineEndings($needle, $haystack);
    }

    public function testNotAssertStringContainsStringIgnoringLineEndings(): void
    {
        $this->expectException(ExpectationFailedException::class);
        Assert::assertStringContainsStringIgnoringLineEndings("b\nc", "\r\nc\r\n");
    }
}
