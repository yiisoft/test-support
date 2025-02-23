<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Log;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Stringable;

use function gettype;
use function implode;
use function is_string;
use function preg_replace_callback;
use function sprintf;

final class SimpleLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * The list of log message levels. See {@see LogLevel} constants for valid level names.
     */
    private const LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    /**
     * @var array[] The log messages.
     */
    private array $messages = [];

    /**
     * Logs a message in an array {@see $messages}.
     *
     * To get all the log messages, use the {@see getMessages()} method.
     *
     * @param mixed $level The log message level.
     * @param string|Stringable $message The log message.
     * @param array $context The log message context.
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        if (!is_string($level)) {
            throw new InvalidArgumentException(sprintf(
                'The log message level must be a string, %s provided.',
                gettype($level)
            ));
        }

        if (!in_array($level, self::LEVELS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid log message level "%s" provided. The following values are supported: "%s".',
                $level,
                implode('", "', self::LEVELS)
            ));
        }

        $message = $this->parseMessage((string)$message, $context);

        $this->messages[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    /**
     * Returns all log messages.
     *
     * @return array[] All log messages.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Parses log message resolving placeholders in the form: "{foo}",
     * where foo will be replaced by the context data in key "foo".
     *
     * @param string $message Raw log message.
     * @param array $context Message context.
     *
     * @return string Parsed message.
     *
     * @psalm-suppress MixedArgumentTypeCoercion
     * @psalm-suppress MixedArrayOffset
     * @psalm-suppress MixedAssignment
     */
    private function parseMessage(string $message, array $context): string
    {
        /**
         * @var string We use correct regular expression, so we expect that `preg_replace_callback` always returns
         * string.
         */
        return preg_replace_callback('/{([\w.]+)}/', static function (array $matches) use ($context) {
            $placeholderName = $matches[1];

            if (isset($context[$placeholderName])) {
                return (string) $context[$placeholderName];
            }

            return $matches[0];
        }, $message);
    }
}
