<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\HttpMessage;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Test\Support\HttpMessage\StringStream;

final class StringStreamTest extends TestCase
{
    public function testBase(): void
    {
        $stream = new StringStream();

        $this->assertSame(0, $stream->getSize());
        $this->assertSame(0, $stream->getPosition());
        $this->assertSame('', (string) $stream);
        $this->assertSame('', $stream->getContents());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->isClosed());
        $this->assertFalse($stream->isDetached());
    }

    public function testConstructorWithContent(): void
    {
        $stream = new StringStream('Hello, World!');

        $this->assertSame('Hello, World!', (string) $stream);
        $this->assertSame(13, $stream->getSize());
    }

    public function testConstructorWithPosition(): void
    {
        $stream = new StringStream('Hello', position: 3);

        $this->assertSame(3, $stream->getPosition());
        $this->assertSame('lo', $stream->getContents());
    }

    public function testConstructorWithReadableFlag(): void
    {
        $stream = new StringStream(readable: false);

        $this->assertFalse($stream->isReadable());
    }

    public function testConstructorWithWritableFlag(): void
    {
        $stream = new StringStream(writable: false);

        $this->assertFalse($stream->isWritable());
    }

    public function testConstructorWithSeekableFlag(): void
    {
        $stream = new StringStream(seekable: false);

        $this->assertFalse($stream->isSeekable());
    }

    public function testToString(): void
    {
        $stream = new StringStream('Test content');

        $this->assertSame('Test content', (string) $stream);
    }

    public function testToStringWhenClosed(): void
    {
        $stream = new StringStream('Test content');
        $stream->close();

        $this->assertSame('Test content', (string) $stream);
    }

    public function testToStringWhenDetached(): void
    {
        $stream = new StringStream('Test content');
        $stream->detach();

        $this->assertSame('Test content', (string) $stream);
    }

    public function testClose(): void
    {
        $stream = new StringStream('content');

        $this->assertFalse($stream->isClosed());

        $stream->close();

        $this->assertTrue($stream->isClosed());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
    }

    public function testDetach(): void
    {
        $stream = new StringStream('content');

        $this->assertFalse($stream->isDetached());

        $result = $stream->detach();

        $this->assertNull($result);
        $this->assertTrue($stream->isDetached());
        $this->assertTrue($stream->isClosed());
    }

    public function testGetSize(): void
    {
        $stream = new StringStream('');
        $this->assertSame(0, $stream->getSize());

        $stream = new StringStream('Hello');
        $this->assertSame(5, $stream->getSize());

        $stream = new StringStream('Привет');
        $this->assertSame(12, $stream->getSize()); // UTF-8 bytes
    }

    public function testTell(): void
    {
        $stream = new StringStream('Hello', position: 0);
        $this->assertSame(0, $stream->tell());

        $stream = new StringStream('Hello', position: 3);
        $this->assertSame(3, $stream->tell());
    }

    public function testTellThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->tell();
    }

    public function testEof(): void
    {
        $stream = new StringStream('Hello', position: 0);
        $this->assertFalse($stream->eof());

        $stream = new StringStream('Hello', position: 5);
        $this->assertTrue($stream->eof());

        $stream = new StringStream('Hello', position: 10);
        $this->assertTrue($stream->eof());
    }

    public function testEofWhenClosed(): void
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->assertTrue($stream->eof());
    }

    public function testSeekSet(): void
    {
        $stream = new StringStream('Hello World');

        $stream->seek(5);
        $this->assertSame(5, $stream->getPosition());

        $stream->seek(0);
        $this->assertSame(0, $stream->getPosition());
    }

    public function testSeekCur(): void
    {
        $stream = new StringStream('Hello World', position: 3);

        $stream->seek(2, SEEK_CUR);
        $this->assertSame(5, $stream->getPosition());

        $stream->seek(-3, SEEK_CUR);
        $this->assertSame(2, $stream->getPosition());
    }

    public function testSeekEnd(): void
    {
        $stream = new StringStream('Hello World');

        $stream->seek(0, SEEK_END);
        $this->assertSame(11, $stream->getPosition());

        $stream->seek(-5, SEEK_END);
        $this->assertSame(6, $stream->getPosition());
    }

    public function testSeekThrowsExceptionWhenNotSeekable(): void
    {
        $stream = new StringStream('Hello', seekable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');
        $stream->seek(0);
    }

    public function testSeekThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->seek(0);
    }

    public function testSeekThrowsExceptionForInvalidWhence(): void
    {
        $stream = new StringStream('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid whence value.');
        $stream->seek(0, 999);
    }

    public function testSeekThrowsExceptionForNegativePosition(): void
    {
        $stream = new StringStream('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid seek position.');
        $stream->seek(-1);
    }

    public function testSeekThrowsExceptionForPositionBeyondSize(): void
    {
        $stream = new StringStream('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid seek position.');
        $stream->seek(100);
    }

    public function testRewind(): void
    {
        $stream = new StringStream('Hello', position: 5);

        $stream->rewind();

        $this->assertSame(0, $stream->getPosition());
    }

    public function testRewindThrowsExceptionWhenNotSeekable(): void
    {
        $stream = new StringStream('Hello', seekable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');
        $stream->rewind();
    }

    public function testRewindThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->rewind();
    }

    public function testWrite(): void
    {
        $stream = new StringStream();

        $bytesWritten = $stream->write('Hello');

        $this->assertSame(5, $bytesWritten);
        $this->assertSame('Hello', (string) $stream);
        $this->assertSame(5, $stream->getPosition());
    }

    public function testWriteAtPosition(): void
    {
        $stream = new StringStream('Hello World', position: 6);

        $stream->write('PHP');

        $this->assertSame('Hello PHPld', (string) $stream);
        $this->assertSame(9, $stream->getPosition());
    }

    public function testWriteOverwrite(): void
    {
        $stream = new StringStream('AAAAA', position: 0);

        $stream->write('BB');

        $this->assertSame('BBAAA', (string) $stream);
    }

    public function testWriteAtEndOfContent(): void
    {
        $stream = new StringStream('Hello', position: 5);

        $bytesWritten = $stream->write(' World');

        $this->assertSame(6, $bytesWritten);
        $this->assertSame('Hello World', (string) $stream);
        $this->assertSame(11, $stream->getPosition());
    }

    public function testWriteBeyondContent(): void
    {
        $stream = new StringStream('Hi', position: 10);

        $bytesWritten = $stream->write('!');

        $this->assertSame(1, $bytesWritten);
        $this->assertSame('Hi!', (string) $stream);
        $this->assertSame(3, $stream->getPosition());
    }

    public function testWriteThrowsExceptionWhenNotWritable(): void
    {
        $stream = new StringStream(writable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');
        $stream->write('test');
    }

    public function testWriteThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream();
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->write('test');
    }

    public function testRead(): void
    {
        $stream = new StringStream('Hello World');

        $result = $stream->read(5);

        $this->assertSame('Hello', $result);
        $this->assertSame(5, $stream->getPosition());
    }

    public function testReadFromPosition(): void
    {
        $stream = new StringStream('Hello World', position: 6);

        $result = $stream->read(5);

        $this->assertSame('World', $result);
    }

    public function testReadBeyondContent(): void
    {
        $stream = new StringStream('Hi');

        $result = $stream->read(100);

        $this->assertSame('Hi', $result);
        $this->assertSame(2, $stream->getPosition());
    }

    public function testReadAtEof(): void
    {
        $stream = new StringStream('Hello', position: 5);

        $result = $stream->read(10);

        $this->assertSame('', $result);
    }

    public function testReadThrowsExceptionWhenNotReadable(): void
    {
        $stream = new StringStream('content', readable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');
        $stream->read(5);
    }

    public function testReadThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream('content');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->read(5);
    }

    public function testGetContents(): void
    {
        $stream = new StringStream('Hello World');

        $result = $stream->getContents();

        $this->assertSame('Hello World', $result);
        $this->assertSame(11, $stream->getPosition());
    }

    public function testGetContentsFromPosition(): void
    {
        $stream = new StringStream('Hello World', position: 6);

        $result = $stream->getContents();

        $this->assertSame('World', $result);
    }

    public function testGetContentsAtEof(): void
    {
        $stream = new StringStream('Hello', position: 5);

        $result = $stream->getContents();

        $this->assertSame('', $result);
    }

    public function testGetContentsThrowsExceptionWhenNotReadable(): void
    {
        $stream = new StringStream('content', readable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');
        $stream->getContents();
    }

    public function testGetContentsThrowsExceptionWhenClosed(): void
    {
        $stream = new StringStream('content');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->getContents();
    }

    public function testGetMetadataDefault(): void
    {
        $stream = new StringStream('Hello');

        $metadata = $stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('eof', $metadata);
        $this->assertArrayHasKey('seekable', $metadata);
        $this->assertFalse($metadata['eof']);
        $this->assertTrue($metadata['seekable']);
    }

    public function testGetMetadataDefaultAtEof(): void
    {
        $stream = new StringStream('Hello', position: 5);

        $metadata = $stream->getMetadata();

        $this->assertTrue($metadata['eof']);
    }

    public function testGetMetadataWithKey(): void
    {
        $stream = new StringStream('Hello');

        $this->assertFalse($stream->getMetadata('eof'));
        $this->assertTrue($stream->getMetadata('seekable'));
        $this->assertNull($stream->getMetadata('nonexistent'));
    }

    public function testGetMetadataWhenClosed(): void
    {
        $stream = new StringStream('Hello');
        $stream->close();

        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('eof'));
    }

    public function testGetMetadataWithArrayMetadata(): void
    {
        $customMetadata = [
            'uri' => 'php://memory',
            'mode' => 'r+',
            'custom' => 'value',
        ];
        $stream = new StringStream('Hello', metadata: $customMetadata);

        $metadata = $stream->getMetadata();

        $this->assertSame($customMetadata, $metadata);
        $this->assertSame('php://memory', $stream->getMetadata('uri'));
        $this->assertSame('value', $stream->getMetadata('custom'));
        $this->assertNull($stream->getMetadata('nonexistent'));
    }

    public function testGetMetadataWithClosureMetadata(): void
    {
        $stream = new StringStream(
            'Hello',
            position: 2,
            metadata: static fn(StringStream $s) => [
                'position' => $s->getPosition(),
                'size' => $s->getSize(),
            ],
        );

        $metadata = $stream->getMetadata();

        $this->assertSame(['position' => 2, 'size' => 5], $metadata);
        $this->assertSame(2, $stream->getMetadata('position'));
        $this->assertSame(5, $stream->getMetadata('size'));
    }

    public function testIsReadableWhenClosed(): void
    {
        $stream = new StringStream();
        $stream->close();

        $this->assertFalse($stream->isReadable());
    }

    public function testIsWritableWhenClosed(): void
    {
        $stream = new StringStream();
        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    public function testIsSeekableWhenClosed(): void
    {
        $stream = new StringStream();
        $stream->close();

        $this->assertFalse($stream->isSeekable());
    }

    public function testMultipleReadOperations(): void
    {
        $stream = new StringStream('Hello World');

        $this->assertSame('Hello', $stream->read(5));
        $this->assertSame(' ', $stream->read(1));
        $this->assertSame('World', $stream->read(5));
        $this->assertSame('', $stream->read(1));
    }

    public function testReadWriteCombination(): void
    {
        $stream = new StringStream('Hello World');

        $stream->read(6);
        $stream->write('PHP');

        $this->assertSame('Hello PHPld', (string) $stream);
    }

    public function testSeekReadCombination(): void
    {
        $stream = new StringStream('Hello World');

        $stream->seek(6);
        $result = $stream->read(5);

        $this->assertSame('World', $result);
    }
}
