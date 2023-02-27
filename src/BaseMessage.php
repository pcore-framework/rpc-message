<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use PCore\RpcMessage\Bags\HeaderBag;
use PCore\RpcMessage\Stream\StandardStream;
use Psr\Http\Message\{MessageInterface, StreamInterface};

/**
 * Class BaseMessage
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/http-message
 */
class BaseMessage implements MessageInterface
{

    /**
     * @var string
     */
    protected string $protocolVersion = '1.1';

    /**
     * @var HeaderBag
     */
    protected HeaderBag $headers;

    /**
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body = null;

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setProtocolVersion($version): static
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * @param $version
     * @return MessageInterface
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $new = clone $this;
        return $new->setProtocolVersion($version);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        if ($this->hasHeader($name)) {
            return implode(', ', $this->getHeader($name));
        }
        return '';
    }

    /**
     * @param $name
     * @return bool|null
     */
    public function hasHeader($name): ?bool
    {
        return $this->headers->has($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @return MessageInterface
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers = clone $this->headers;
        return $new->setHeader($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;
        $new->headers = clone $this->headers;
        return $new->setAddedHeader($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAddedHeader($name, $value)
    {
        $this->headers->add($name, $value);
        return $this;
    }

    /**
     * @param $name
     * @return BaseMessage
     */
    public function withoutHeader($name)
    {
        $new = clone $this;
        $new->headers = clone $this->headers;
        $new->headers->remove($name);
        return $new;
    }

    /**
     * @return StreamInterface|null
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     * @return $this
     */
    public function setBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        return $new->setBody($body);
    }

    /**
     * @param string|StreamInterface|null $body
     */
    protected function formatBody(string|StreamInterface|null $body)
    {
        $this->body = $body ? StandardStream::create($body) : null;
    }

}
