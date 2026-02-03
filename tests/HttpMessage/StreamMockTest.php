<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\HttpMessage;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Test\Support\HttpMessage\StreamMock;

final class StreamMockTest extends TestCase
{
    public function testBase(): void
    {
        $stream = new StreamMock();

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
        $stream = new StreamMock('Hello, World!');

        $this->assertSame('Hello, World!', (string) $stream);
        $this->assertSame(13, $stream->getSize());
    }

    public function testConstructorWithPosition(): void
    {
        $stream = new StreamMock('Hello', position: 3);

        $this->assertSame(3, $stream->getPosition());
        $this->assertSame('lo', $stream->getContents());
    }

    public function testConstructorWithReadableFlag(): void
    {
        $stream = new StreamMock(readable: false);

        $this->assertFalse($stream->isReadable());
    }

    public function testConstructorWithWritableFlag(): void
    {
        $stream = new StreamMock(writable: false);

        $this->assertFalse($stream->isWritable());
    }

    public function testConstructorWithSeekableFlag(): void
    {
        $stream = new StreamMock(seekable: false);

        $this->assertFalse($stream->isSeekable());
    }

    public function testToString(): void
    {
        $stream = new StreamMock('Test content');

        $this->assertSame('Test content', (string) $stream);
    }

    public function testClose(): void
    {
        $stream = new StreamMock('content');

        $this->assertFalse($stream->isClosed());

        $stream->close();

        $this->assertTrue($stream->isClosed());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
    }

    public function testDetach(): void
    {
        $stream = new StreamMock('content');

        $this->assertFalse($stream->isDetached());

        $result = $stream->detach();

        $this->assertNull($result);
        $this->assertTrue($stream->isDetached());
        $this->assertTrue($stream->isClosed());
    }

    public function testGetSize(): void
    {
        $stream = new StreamMock('');
        $this->assertSame(0, $stream->getSize());

        $stream = new StreamMock('Hello');
        $this->assertSame(5, $stream->getSize());

        $stream = new StreamMock('Привет');
        $this->assertSame(12, $stream->getSize()); // UTF-8 bytes
    }

    public function testTell(): void
    {
        $stream = new StreamMock('Hello', position: 0);
        $this->assertSame(0, $stream->tell());

        $stream = new StreamMock('Hello', position: 3);
        $this->assertSame(3, $stream->tell());
    }

    public function testTellThrowsExceptionWhenClosed(): void
    {
        $stream = new StreamMock('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->tell();
    }

    public function testEof(): void
    {
        $stream = new StreamMock('Hello', position: 0);
        $this->assertFalse($stream->eof());

        $stream = new StreamMock('Hello', position: 5);
        $this->assertTrue($stream->eof());

        $stream = new StreamMock('Hello', position: 10);
        $this->assertTrue($stream->eof());
    }

    public function testEofWhenClosed(): void
    {
        $stream = new StreamMock('Hello');
        $stream->close();

        $this->assertTrue($stream->eof());
    }

    public function testSeekSet(): void
    {
        $stream = new StreamMock('Hello World');

        $stream->seek(5);
        $this->assertSame(5, $stream->getPosition());

        $stream->seek(0);
        $this->assertSame(0, $stream->getPosition());
    }

    public function testSeekCur(): void
    {
        $stream = new StreamMock('Hello World', position: 3);

        $stream->seek(2, SEEK_CUR);
        $this->assertSame(5, $stream->getPosition());

        $stream->seek(-3, SEEK_CUR);
        $this->assertSame(2, $stream->getPosition());
    }

    public function testSeekEnd(): void
    {
        $stream = new StreamMock('Hello World');

        $stream->seek(0, SEEK_END);
        $this->assertSame(11, $stream->getPosition());

        $stream->seek(-5, SEEK_END);
        $this->assertSame(6, $stream->getPosition());
    }

    public function testSeekThrowsExceptionWhenNotSeekable(): void
    {
        $stream = new StreamMock('Hello', seekable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');
        $stream->seek(0);
    }

    public function testSeekThrowsExceptionWhenClosed(): void
    {
        $stream = new StreamMock('Hello');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->seek(0);
    }

    public function testSeekThrowsExceptionForInvalidWhence(): void
    {
        $stream = new StreamMock('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid whence value.');
        $stream->seek(0, 999);
    }

    public function testSeekThrowsExceptionForNegativePosition(): void
    {
        $stream = new StreamMock('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid seek position.');
        $stream->seek(-1);
    }

    public function testSeekThrowsExceptionForPositionBeyondSize(): void
    {
        $stream = new StreamMock('Hello');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid seek position.');
        $stream->seek(100);
    }

    public function testRewind(): void
    {
        $stream = new StreamMock('Hello', position: 5);

        $stream->rewind();

        $this->assertSame(0, $stream->getPosition());
    }

    public function testWrite(): void
    {
        $stream = new StreamMock();

        $bytesWritten = $stream->write('Hello');

        $this->assertSame(5, $bytesWritten);
        $this->assertSame('Hello', (string) $stream);
        $this->assertSame(5, $stream->getPosition());
    }

    public function testWriteAtPosition(): void
    {
        $stream = new StreamMock('Hello World', position: 6);

        $stream->write('PHP');

        $this->assertSame('Hello PHPld', (string) $stream);
        $this->assertSame(9, $stream->getPosition());
    }

    public function testWriteOverwrite(): void
    {
        $stream = new StreamMock('AAAAA', position: 0);

        $stream->write('BB');

        $this->assertSame('BBAAA', (string) $stream);
    }

    public function testWriteThrowsExceptionWhenNotWritable(): void
    {
        $stream = new StreamMock(writable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');
        $stream->write('test');
    }

    public function testWriteThrowsExceptionWhenClosed(): void
    {
        $stream = new StreamMock();
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->write('test');
    }

    public function testRead(): void
    {
        $stream = new StreamMock('Hello World');

        $result = $stream->read(5);

        $this->assertSame('Hello', $result);
        $this->assertSame(5, $stream->getPosition());
    }

    public function testReadFromPosition(): void
    {
        $stream = new StreamMock('Hello World', position: 6);

        $result = $stream->read(5);

        $this->assertSame('World', $result);
    }

    public function testReadBeyondContent(): void
    {
        $stream = new StreamMock('Hi');

        $result = $stream->read(100);

        $this->assertSame('Hi', $result);
        $this->assertSame(2, $stream->getPosition());
    }

    public function testReadAtEof(): void
    {
        $stream = new StreamMock('Hello', position: 5);

        $result = $stream->read(10);

        $this->assertSame('', $result);
    }

    public function testReadThrowsExceptionWhenNotReadable(): void
    {
        $stream = new StreamMock('content', readable: false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');
        $stream->read(5);
    }

    public function testReadThrowsExceptionWhenClosed(): void
    {
        $stream = new StreamMock('content');
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->read(5);
    }

    public function testGetContents(): void
    {
        $stream = new StreamMock('Hello World');

        $result = $stream->getContents();

        $this->assertSame('Hello World', $result);
        $this->assertSame(11, $stream->getPosition());
    }

    public function testGetContentsFromPosition(): void
    {
        $stream = new StreamMock('Hello World', position: 6);

        $result = $stream->getContents();

        $this->assertSame('World', $result);
    }

    public function testGetContentsAtEof(): void
    {
        $stream = new StreamMock('Hello', position: 5);

        $result = $stream->getContents();

        $this->assertSame('', $result);
    }

    public function testGetMetadataDefault(): void
    {
        $stream = new StreamMock('Hello');

        $metadata = $stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('eof', $metadata);
        $this->assertArrayHasKey('seekable', $metadata);
        $this->assertFalse($metadata['eof']);
        $this->assertTrue($metadata['seekable']);
    }

    public function testGetMetadataDefaultAtEof(): void
    {
        $stream = new StreamMock('Hello', position: 5);

        $metadata = $stream->getMetadata();

        $this->assertTrue($metadata['eof']);
    }

    public function testGetMetadataWithKey(): void
    {
        $stream = new StreamMock('Hello');

        $this->assertFalse($stream->getMetadata('eof'));
        $this->assertTrue($stream->getMetadata('seekable'));
        $this->assertNull($stream->getMetadata('nonexistent'));
    }

    public function testGetMetadataWhenClosed(): void
    {
        $stream = new StreamMock('Hello');
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
        $stream = new StreamMock('Hello', metadata: $customMetadata);

        $metadata = $stream->getMetadata();

        $this->assertSame($customMetadata, $metadata);
        $this->assertSame('php://memory', $stream->getMetadata('uri'));
        $this->assertSame('value', $stream->getMetadata('custom'));
        $this->assertNull($stream->getMetadata('nonexistent'));
    }

    public function testGetMetadataWithClosureMetadata(): void
    {
        $stream = new StreamMock(
            'Hello',
            position: 2,
            metadata: static fn(StreamMock $s) => [
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
        $stream = new StreamMock();
        $stream->close();

        $this->assertFalse($stream->isReadable());
    }

    public function testIsWritableWhenClosed(): void
    {
        $stream = new StreamMock();
        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    public function testIsSeekableWhenClosed(): void
    {
        $stream = new StreamMock();
        $stream->close();

        $this->assertFalse($stream->isSeekable());
    }

    public function testMultipleReadOperations(): void
    {
        $stream = new StreamMock('Hello World');

        $this->assertSame('Hello', $stream->read(5));
        $this->assertSame(' ', $stream->read(1));
        $this->assertSame('World', $stream->read(5));
        $this->assertSame('', $stream->read(1));
    }

    public function testReadWriteCombination(): void
    {
        $stream = new StreamMock('Hello World');

        $stream->read(6);
        $stream->write('PHP');

        $this->assertSame('Hello PHPld', (string) $stream);
    }

    public function testSeekReadCombination(): void
    {
        $stream = new StreamMock('Hello World');

        $stream->seek(6);
        $result = $stream->read(5);

        $this->assertSame('World', $result);
    }
}
