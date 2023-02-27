<?php

declare(strict_types=1);

namespace PCore\RpcMessage;

use JsonSerializable;
use function get_object_vars;

/**
 * Class Error
 * @package PCore\RpcMessage
 * @github https://github.com/pcore-framework/rpc-message
 */
class Error implements JsonSerializable
{

    public function __construct(protected int $code, protected string $message)
    {
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

}
