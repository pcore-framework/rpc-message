<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/json-message
 */
class Uri implements UriInterface
{

    /**
     * Порт по умолчанию
     */
    protected const DEFAULT_PORT = ['https' => 443, 'http' => 80];
    /**
     * @var string
     */
    protected string $path = '/';
    /**
     * @var string
     */
    protected string $scheme = 'http';
    /**
     * @var string
     */
    protected string $host = 'localhost';
    /**
     * @var int|string
     */
    protected int|string $port = 80;
    /**
     * @var string
     */
    protected string $query = '';
    /**
     * @var string
     */
    protected string $fragment = '';
    /**
     * @var string
     */
    protected string $authority = '';
    /**
     * @var string|mixed
     */
    protected string $userinfo = '';

    /**
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            if (false === $parts = parse_url($uri)) {
                throw new InvalidArgumentException("Невозможно проанализировать URI: {$uri}");
            }
            if (isset($parts['scheme'])) {
                $this->scheme = $parts['scheme'];
            }
            if (isset($parts['user'])) {
                $this->userinfo = isset($parts['pass']) ? sprintf('%s:%s', $parts['user'], $parts['pass']) : $parts['user'];
            }
            if (isset($parts['host'])) {
                $this->host = $parts['host'];
            }
            $this->port = $parts['port'] ?? $this->getDefaultPort();
            if (isset($parts['path'])) {
                $this->path = '/' . trim($parts['path'], '/');
            }
            if (isset($parts['query'])) {
                $this->query = $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $this->fragment = $parts['fragment'];
            }
            if ($this->userinfo !== '') {
                $port = ($this->port > 655535 || $this->port < 0) ? '' : $this->getPortString();
                $this->authority = $this->userinfo . '@' . $this->host . $port;
            }
        }
    }

    /**
     * @return int|null
     */
    public function getDefaultPort(): ?int
    {
        return self::DEFAULT_PORT[$this->scheme] ?? null;
    }

    /**
     * @return string
     */
    protected function getPortString(): string
    {
        if (($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443)) {
            return '';
        }
        return ':' . $this->port;
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        return $this->authority;
    }

    /**
     * @return string|void
     */
    public function getUserInfo(): string
    {
        return $this->userinfo;
    }

    /**
     * @return int|string
     */
    public function getPort(): int|string|null
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param $scheme
     * @return UriInterface
     */
    public function withScheme($scheme): UriInterface
    {
        if ($scheme === $this->scheme) {
            return $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    /**
     * @param $user
     * @param null $password
     * @return UriInterface
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $new = clone $this;
        $new->userinfo = sprintf('%s%s', $user, $password ? (':' . $password) : '');
        return $new;
    }

    /**
     * @param $host
     * @return UriInterface
     */
    public function withHost($host): UriInterface
    {
        if ($host === $this->host) {
            return $this;
        }
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * @param $port
     * @return UriInterface
     */
    public function withPort($port): UriInterface
    {
        if ($port === $this->port) {
            return $this;
        }
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * @param $path
     * @return UriInterface
     */
    public function withPath($path): UriInterface
    {
        if ($path === $this->path) {
            return $this;
        }
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * @param $query
     * @return UriInterface
     */
    public function withQuery($query): UriInterface
    {
        if ($query === $this->query) {
            return $this;
        }
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    /**
     * @param $fragment
     * @return UriInterface
     */
    public function withFragment($fragment): UriInterface
    {
        if ($fragment === $this->fragment) {
            return $this;
        }
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s://%s%s%s%s%s',
            $this->getScheme(),
            $this->getHost(),
            $this->getPortString(),
            $this->getPath(),
            ($this->query === '') ? '' : ('?' . $this->query),
            ($this->fragment === '') ? '' : ('#' . $this->fragment),
        );
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

}
