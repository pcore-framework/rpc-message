<?php

declare(strict_types=1);

namespace PCore\RpcMessage\Contracts;

use Psr\Http\Message\{StreamInterface, UriInterface};

/**
 * Interface ServerRequestInterface
 * @package PCore\RpcMessage\Contracts
 * @github https://github.com/pcore-framework/rpc-message
 */
interface ServerRequestInterface
{

    /**
     * @return array
     */
    public function getServerParams(): array;

    /**
     * @param string $name
     * @return bool|null
     */
    public function hasHeader(string $name): ?bool;

    /**
     * @return mixed
     */
    public function getHeaders(): mixed;

    /**
     * @param string $name
     * @return mixed
     */
    public function getHeader(string $name): mixed;

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return StreamInterface|null
     */
    public function getBody(): ?StreamInterface;

    /**
     * @return string
     */
    public function getRpcMethod(): string;

    /**
     * @param string $rpcMethod
     * @return void
     */
    public function setRpcMethod(string $rpcMethod): void;

    /**
     * @return string
     */
    public function getRealIp(): string;

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * @return string
     */
    public function url(): string;

    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function withAttribute(string $name, $value): mixed;

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null): mixed;

}
