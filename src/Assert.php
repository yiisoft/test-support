<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support;

use PHPUnit\Framework\Assert as PhpUnitAssert;
use PHPUnit\Framework\ExpectationFailedException;

final class Assert
{
    /**
     * Asserts that two strings equality ignoring line endings.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEqualIgnoringLineEndings(string $expected, string $actual, string $message = ''): void
    {
        $expected = self::normalizeLineEndings($expected);
        $actual = self::normalizeLineEndings($actual);

        PhpUnitAssert::assertEquals($expected, $actual, $message);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringContainsStringIgnoringLineEndings(
        string $needle,
        string $haystack,
        string $message = ''
    ): void {
        $needle = self::normalizeLineEndings($needle);
        $haystack = self::normalizeLineEndings($haystack);

        PhpUnitAssert::assertStringContainsString($needle, $haystack, $message);
    }

    private static function normalizeLineEndings(string $value): string
    {
        return strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n",
        ]);
    }
}
