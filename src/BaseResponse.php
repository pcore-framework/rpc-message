<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use PCore\RpcMessage\Bags\HeaderBag;
use Psr\Http\Message\{ResponseInterface, StreamInterface};

/**
 * Class BaseResponse
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/http-message
 */
class BaseResponse extends BaseMessage implements ResponseInterface
{

    protected const PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    protected string $reasonPhrase = '';

    public function __construct(
        protected int               $statusCode = 200,
        array                       $headers = [],
        null|string|StreamInterface $body = null,
        protected string            $protocolVersion = '1.1'
    )
    {
        $this->reasonPhrase = static::PHRASES[$statusCode] ?? '';
        $this->formatBody($body);
        $this->headers = new HeaderBag($headers);
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if ($code === $this->statusCode) {
            return $this;
        }
        $new = clone $this;
        return $new->setStatusCode($code, $reasonPhrase);
    }

    /**
     * @param $code
     * @param string $reasonPhrase
     * @return $this
     */
    public function setStatusCode($code, $reasonPhrase = ''): static
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase ?: (self::PHRASES[$code] ?? '');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase ?: (self::PHRASES[$this->getStatusCode()] ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isInvalid(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * @return bool
     */
    public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * @return bool
     */
    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * @return bool
     */
    public function isForbidden(): bool
    {
        return $this->statusCode === 403;
    }

    /**
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * @param string|null $location
     * @return bool
     */
    public function isRedirect(string $location = null): bool
    {
        return in_array($this->statusCode, [201, 301, 302, 303, 307, 308])
            && ($location === null || $location == $this->getHeaderLine('Location'));
    }

}
