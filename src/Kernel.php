<?php

declare(strict_types=1);

namespace PCore\RpcServer;

use BadMethodCallException;
use InvalidArgumentException;
use PCore\Di\Reflection;
use PCore\HttpMessage\Response as PsrResponse;
use PCore\HttpMessage\Stream\StandardStream;
use PCore\RpcServer\Contracts\KernelInterface;
use PCore\Utils\Arr;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use ReflectionException;
use ReflectionMethod;
use Throwable;

/**
 * Class Kernel
 * @package PCore\RpcServer
 * @github https://github.com/pcore-framework/rpc-server
 */
class Kernel implements KernelInterface
{

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $rpcRequest = Request::createFromPsrRequest($request);
            if (is_null($service = $this->getService($rpcRequest->getMethod()))) {
                throw new BadMethodCallException('Метод не найден', -32601);
            }
            $result = call($service, $rpcRequest->getParams());
            $psrResponse = new PsrResponse();
            if ($rpcRequest->hasId()) {
                $psrResponse = $psrResponse
                    ->withHeader('Content-Type', 'application/json; charset=utf-8')
                    ->withBody(StandardStream::create(json_encode([
                        'jsonrpc' => $rpcRequest->getJsonRpc(),
                        'result' => $result,
                        'id' => $rpcRequest->getId()
                    ])));
            }
            return $psrResponse;
        } catch (Throwable $e) {
            $psrResponse = new PsrResponse();
            if (!isset($rpcRequest) || ($rpcRequest->hasId())) {
                $rpcResponse = new Response(null, isset($rpcRequest) ? $rpcRequest->getId() : null,
                    new Error($e->getCode(), $e->getMessage())
                );
                $psrResponse = $psrResponse
                    ->withHeader('Content-Type', 'application/json; charset=utf-8')
                    ->withBody(StandardStream::create(json_encode($rpcResponse, JSON_UNESCAPED_UNICODE)));
            }
            return $psrResponse;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getService(string $name): mixed
    {
        return Arr::get($this->services, $name);
    }

    /**
     * @param string $name
     * @param string $class
     * @return void
     * @throws ReflectionException
     */
    public function register(string $name, string $class): void
    {
        if (isset($this->services[$name])) {
            throw new InvalidArgumentException('Сервис \'' . $name . '\' был зарегистрирован');
        }
        foreach (Reflection::methods($class, ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            $reflectionMethodName = $reflectionMethod->getName();
            $this->services[$name][$reflectionMethodName] = [$class, $reflectionMethodName];
        }
    }

}
