<?php

namespace PCore\RpcMessage\Stream;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use function clearstatcache;
use function fclose;
use function feof;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function is_resource;
use function is_string;
use function stream_get_contents;
use function stream_get_meta_data;
use function var_export;
use const SEEK_CUR;
use const SEEK_SET;

/**
 * Class StandardStream
 * @package PCore\RpcMessage\Stream
 * @github https://github.com/pcore-framework/rpc-message
 */
final class StandardStream implements StreamInterface
{

    /**
     * @var array
     */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
        ]
    ];

    /**
     * @var null|resource
     */
    private $stream;

    /**
     * @var bool
     */
    private bool $seekable;

    /**
     * @var bool
     */
    private bool $readable;

    /**
     * @var bool
     */
    private bool $writable;

    /**
     * @var null|array|mixed|void
     */
    private $uri;

    /**
     * @var int|null
     */
    private ?int $size;

    private function __construct()
    {
    }

    /**
     * @param mixed|string $body
     * @return StreamInterface
     */
    public static function create(mixed $body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        if (is_string($body)) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, $body);
            rewind($resource);
            $body = $resource;
        }
        if (is_resource($body)) {
            $new = new self();
            $new->stream = $body;
            $meta = stream_get_meta_data($new->stream);
            $new->seekable = $meta['seekable'] && fseek($new->stream, 0, SEEK_CUR) === 0;
            $new->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
            $new->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);
            $new->uri = $new->getMetadata('uri');
            return $new;
        }
        throw new InvalidArgumentException('Первый аргумент Stream::create() должен быть строкой, ресурсом или интерфейсом StreamInterface.');
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }
        $meta = stream_get_meta_data($this->stream);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (Exception) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new RuntimeException('Поток не доступен для поиска');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Невозможно найти позицию в потоке ' . $offset . ' откуда ' . var_export($whence, true));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Невозможно прочитать содержимое потока');
        }
        if (false === $contents = stream_get_contents($this->stream)) {
            throw new RuntimeException('Невозможно прочитать содержимое потока');
        }
        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!isset($this->stream)) {
            return null;
        }
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        if (false === $result = ftell($this->stream)) {
            throw new RuntimeException('Не удалось определить позицию потока');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritDoc}
     */
    public function write($string): int
    {
        if (!$this->writable) {
            throw new RuntimeException('Не удается записать в недоступный для записи поток');
        }
        $this->size = null;
        if (false === $result = fwrite($this->stream, $string)) {
            throw new RuntimeException('Невозможно записать в поток');
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Невозможно прочитать из нечитаемого потока');
        }
        return fread($this->stream, $length);
    }

}
