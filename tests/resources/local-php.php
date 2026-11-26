<?php

use Fusio\Engine;
use Fusio\Worker;
use Psr\Log\LoggerInterface;
use PSX\Http\Environment\HttpResponseInterface;

return function (Worker\ExecuteRequest $request, Worker\ExecuteContext $context, Engine\ConnectorInterface $connector, Engine\Response\FactoryInterface $response, Engine\DispatcherInterface $dispatcher, LoggerInterface $logger): HttpResponseInterface {
    $logger->error('This is an error message', ['foo' => 'bar']);

    return $response->ok([
        'foo' => 'bar',
    ]);
};

