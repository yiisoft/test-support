<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\HttpMessage;

use Closure;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use LogicException;

use function is_array;
use function strlen;

/**
 * A test-specific implementation of PSR-7 stream.
 *
 * Allows creating stream instances with configurable behavior for testing HTTP message handling.
 *
 * @psalm-type MetadataClosure = Closure(StringStream): array<string, mixed>
 */
final class StringStream implements StreamInterface
{
    private bool $closed = false;
    private bool $detached = false;

    /**
     * @param string $content Initial stream content.
     * @param int $position Initial position of the stream pointer.
     * @param bool $readable Whether the stream is readable.
     * @param bool $writable Whether the stream is writable.
     * @param bool $seekable Whether the stream is seekable.
     * @param array|Closure|null $metadata Custom metadata as an array or a closure that receives
     * the stream instance and returns an array.
     *
     * @psalm-param MetadataClosure|array|null $metadata
     */
    public function __construct(
        private string $content = '',
        private int $position = 0,
        private bool $readable = true,
        private bool $writable = true,
        private bool $seekable = true,
        private Closure|array|null $metadata = null,
    ) {
        $size = strlen($this->content);
        if ($this->position < 0 || $this->position > $size) {
            throw new LogicException(
                sprintf('Position %d is out of valid range [0, %d].', $this->position, $size)
            );
        }
    }

    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Checks whether the stream has been closed.
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Checks whether the stream has been detached.
     */
    public function isDetached(): bool
    {
        return $this->detached;
    }

    /**
     * Returns the current position of the stream pointer.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function detach()
    {
        $this->detached = true;
        $this->close();
        return null;
    }

    public function getSize(): ?int
    {
        return $this->getContentSize();
    }

    public function tell(): int
    {
        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        return $this->position;
    }

    public function eof(): bool
    {
        return $this->closed || $this->position >= $this->getContentSize();
    }

    public function isSeekable(): bool
    {
        return $this->seekable && !$this->closed;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable.');
        }

        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        $size = $this->getContentSize();

        $newPosition = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $size + $offset,
            default => throw new RuntimeException('Invalid whence value.'),
        };

        if ($newPosition < 0 || $newPosition > $size) {
            throw new RuntimeException('Invalid seek position.');
        }

        $this->position = $newPosition;
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable && !$this->closed;
    }

    public function write(string $string): int
    {
        if (!$this->writable) {
            throw new RuntimeException('Stream is not writable.');
        }

        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        $size = strlen($string);
        $this->content = substr($this->content, 0, $this->position)
            . $string
            . substr($this->content, $this->position + $size);
        $this->position = min($this->position + $size, $this->getContentSize());

        return $size;
    }

    public function isReadable(): bool
    {
        return $this->readable && !$this->closed;
    }

    public function read(int $length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable.');
        }

        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        if ($length < 0) {
            throw new RuntimeException('Length cannot be negative.');
        }

        if ($this->position >= $this->getContentSize()) {
            return '';
        }

        $result = substr($this->content, $this->position, $length);
        $this->position += strlen($result);

        return $result;
    }

    public function getContents(): string
    {
        return $this->read(
            $this->getContentSize() - $this->position,
        );
    }

    public function getMetadata(?string $key = null)
    {
        if ($this->closed) {
            return $key === null ? [] : null;
        }

        $metadata = match (true) {
            is_array($this->metadata) => $this->metadata,
            $this->metadata instanceof Closure => ($this->metadata)($this),
            default => [
                'eof' => $this->eof(),
                'seekable' => $this->isSeekable(),
            ],
        };

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }

    private function getContentSize(): int
    {
        return strlen($this->content);
    }
}
