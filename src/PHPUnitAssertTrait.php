<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support;

trait PHPUnitAssertTrait
{
    public function assertEqualStringsIgnoringLineEndings(
        string $expected,
        string $actual,
        string $message = ''
    ): void {
        $expected = self::normalizeLineEndings($expected);
        $actual = self::normalizeLineEndings($actual);

        $this->assertEquals($expected, $actual, $message);
    }

    public function assertStringContainsStringIgnoringLineEndings(
        string $needle,
        string $haystack,
        string $message = ''
    ): void {
        $needle = self::normalizeLineEndings($needle);
        $haystack = self::normalizeLineEndings($haystack);

        $this->assertStringContainsString($needle, $haystack, $message);
    }

    private function normalizeLineEndings(string $value): string
    {
        return strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n",
        ]);
    }
}
