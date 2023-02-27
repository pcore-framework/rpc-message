<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use PCore\RpcMessage\Bags\{HeaderBag, ParameterBag, ServerBag};
use PCore\RpcMessage\Contracts\ServerRequestInterface;
use PCore\RpcMessage\Stream\StandardStream;
use Psr\Http\Message\{StreamInterface, UriInterface};

/**
 * Class ServerRequest
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/json-message
 */
class ServerRequest implements ServerRequestInterface
{

    /**
     * @var ServerBag
     */
    protected ServerBag $serverParams;

    /**
     * @var ParameterBag
     */
    protected ParameterBag $attributes;

    /**
     * @var HeaderBag
     */
    protected HeaderBag $headers;

    /**
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body = null;

    protected string $rpcMethod;

    public function __construct(
        protected string|UriInterface $uri,
        protected string              $method,
        array                         $headers = []
    )
    {
        $this->serverParams = new ServerBag();
        $this->attributes = new ParameterBag();
        $this->headers = new HeaderBag($headers);
    }

    /**
     * @param $request
     * @param array $attributes
     * @return static
     */
    public static function createRequest($request, array $attributes = []): ServerRequestInterface
    {
        $server = $request->server;
        $header = $request->header;
        $uri = (new Uri())->withScheme(isset($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');
        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        } else if (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } else if (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } else if (isset($header['host'])) {
            $hasPort = true;
            if (strpos($header['host'], ':')) {
                [$host, $port] = explode(':', $header['host'], 2);
                if ($port != $uri->getDefaultPort()) {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }
            $uri = $uri->withHost($host);
        }
        if (!$hasPort && isset($server['server_port'])) {
            $uri = $uri->withPort($server['server_port']);
        }
        $hasQuery = false;
        if (isset($server['request_uri'])) {
            $requestUriParts = explode('?', $server['request_uri']);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }
        if (!$hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }
        $psrRequest = new static($uri, $request->getMethod(), $header);
        $psrRequest->serverParams = new ServerBag($server);
        $psrRequest->attributes = new ParameterBag($attributes);
        $psrRequest->body = StandardStream::create((string)$request->getContent());
        return $psrRequest;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * @return StreamInterface|null
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getRpcMethod(): string
    {
        return $this->rpcMethod;
    }

    /**
     * @param string $rpcMethod
     * @return void
     */
    public function setRpcMethod(string $rpcMethod): void
    {
        $this->rpcMethod = $rpcMethod;
    }

    /**
     * @return string
     */
    public function getRealIp(): string
    {
        if ($xForwardedFor = $this->getHeaderLine('X-Forwarded-For')) {
            $ips = explode(',', $xForwardedFor);
            return trim($ips[0]);
        }
        if ($xRealIp = $this->getHeaderLine('X-Real-IP')) {
            return $xRealIp;
        }
        $serverParams = $this->getServerParams();
        return $serverParams['remote_addr'] ?? '127.0.0.1';
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        if ($this->hasHeader($name)) {
            return implode(', ', $this->getHeader($name));
        }
        return '';
    }

    /**
     * @param string $name
     * @return bool|null
     */
    public function hasHeader(string $name): ?bool
    {
        return $this->headers->has($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getHeader(string $name): mixed
    {
        return $this->headers->get($name);
    }

    /**
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams->all();
    }

    /**
     * @return string
     */
    public function url(): string
    {
        $uri = $this->getUri();
        $url = $uri->getPath();
        if (!empty($query = $uri->getQuery())) {
            $url .= '?' . $query;
        }
        return $url;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @param string $name
     * @param $value
     * @return ServerRequestInterface
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->set($name, $value);
        return $new;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute(string $name, $default = null): mixed
    {
        return $this->attributes->get($name, $default);
    }

}
