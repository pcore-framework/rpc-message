<?php

declare(strict_types=1);

namespace PCore\RpcMessage\Bags;

/**
 * Class ServerBag
 * @package PCore\RpcMessage\Bags
 * @github https://github.com/pcore-framework/json-message
 */
class ServerBag extends ParameterBag
{

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];
        if (isset($headers['AUTHORIZATION'])) {
            return $headers;
        }
        return $headers;
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function replace(array $parameters = []): void
    {
        $this->parameters = array_change_key_case($parameters, CASE_UPPER);
    }

}
