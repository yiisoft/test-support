<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Log;

use JsonException;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Throwable;

use function get_class;
use function gettype;
use function implode;
use function is_string;
use function json_encode;
use function method_exists;
use function preg_replace_callback;
use function sprintf;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

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
     * @param mixed $message The log message.
     * @param array $context The log message context.
     */
    public function log($level, $message, array $context = []): void
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

        if (($message instanceof Throwable) && !isset($context['exception'])) {
            $context['exception'] = $message;
        }

        $message = $this->convertMessageToString($message);
        $message = $this->parseMessage($message, $context);

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
     * Converts a message to a string.
     *
     * @param mixed $message The log message to be converted.
     *
     * @return string The log message in the string representation.
     */
    private function convertMessageToString($message): string
    {
        switch (gettype($message)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return $message ? 'true' : 'false';
            case 'object':
                return method_exists($message, '__toString') ? (string) $message : get_class($message);
            case 'array':
                try {
                    return json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } catch (JsonException $e) {
                    return '[]';
                }
            default:
                return (string) $message;
        }
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
        return preg_replace_callback('/{([\w.]+)}/', static function (array $matches) use ($context) {
            $placeholderName = $matches[1];

            if (isset($context[$placeholderName])) {
                return (string) $context[$placeholderName];
            }

            return $matches[0];
        }, $message);
    }
}
