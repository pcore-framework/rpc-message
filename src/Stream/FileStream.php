<?php

declare(strict_types=1);

namespace PCore\RpcMessage\Stream;

use BadMethodCallException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SplFileInfo;
use Throwable;

/**
 * Class FileStream
 * @package PCore\RpcMessage\Stream
 * @github https://github.com/pcore-framework/rpc-message
 */
class FileStream implements StreamInterface
{

    /**
     * @var int
     */
    protected int $size;

    /**
     * @var SplFileInfo|string
     */
    protected SplFileInfo $file;

    /**
     * @var resource $resource
     */
    protected $resource;

    public function __construct(SplFileInfo|string $file, protected int $offset = 0, protected int $length = 0)
    {
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }
        if (!$file->isReadable()) {
            throw new RuntimeException('Файл должен быть доступен для чтения.');
        }
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getContents()
    {
        if (false === $contents = stream_get_contents($this->getResource(), $this->getLength() ?: null, $this->getOffset() ?: -1)) {
            throw new RuntimeException('Невозможно прочитать содержимое потока');
        }
        return $contents;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        if (is_null($this->resource)) {
            $filename = $this->getFilename();
            if (!$this->resource = fopen($filename, 'r')) {
                throw new RuntimeException('Не удалось открыть файл: ' . $filename);
            }
            fseek($this->resource, $this->offset);
        }
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->file->getPathname();
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if (isset($this->resource)) {
            if (is_resource($this->resource)) {
                fclose($this->resource);
            }
        }
    }

    /**
     * @return null|resource
     */
    public function detach()
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @return null|int
     */
    public function getSize()
    {
        if (!$this->size) {
            $this->size = filesize($this->getContents());
        }
        return $this->size;
    }

    /**
     * @return int
     * @throws RuntimeException
     */
    public function tell()
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @return bool
     */
    public function eof()
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @param $offset
     * @param int $whence
     * @throws RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @throws RuntimeException
     */
    public function rewind()
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @param string $string
     * @return int
     * @throws RuntimeException
     */
    public function write($string)
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @param int $length
     * @return string
     * @throws RuntimeException
     */
    public function read($length)
    {
        throw new BadMethodCallException('Не реализованы');
    }

    /**
     * @param string $key
     * @return null|array|mixed
     */
    public function getMetadata($key = null)
    {
        $resource = $this->getResource();
        if (!isset($resource)) {
            return $key ? null : [];
        }
        $meta = stream_get_meta_data($resource);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }

}
