<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use InvalidArgumentException;
use JsonSerializable;
use PCore\RpcMessage\Contracts\ServerRequestInterface;

/**
 * Class Request
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/rpc-message
 */
class Request implements JsonSerializable
{

    public function __construct(
        protected string $method,
        protected array  $params = [],
        protected mixed  $id = null,
        protected string $jsonrpc = '2.0',
    )
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return static
     */
    public static function createFromPsrRequest(ServerRequestInterface $request): static
    {
        if (!str_contains($request->getHeaderLine('Content-Type'), 'application/json')) {
            throw new InvalidArgumentException('Неверный запрос', -32600);
        }
        $body = $request->getBody()->getContents();
        $parts = json_decode($body, true);
        if (!isset($parts['jsonrpc'], $parts['method'])) {
            throw new InvalidArgumentException('Ошибка синтаксического анализа', -32700);
        }
        $request->setRpcMethod($parts['method']);
        return new static($parts['method'], $parts['params'] ?? [], $parts['id'] ?? null, $parts['jsonrpc']);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getJsonRpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @param string $jsonrpc
     * @return void
     */
    public function setJsonRpc(string $jsonrpc): void
    {
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return isset($this->id);
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return void
     */
    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

}
