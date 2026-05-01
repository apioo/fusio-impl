<?php

use Fusio\Worker;
use Fusio\Engine;
use Psr\Log\LoggerInterface;
use PSX\Http\Environment\HttpResponseInterface;

return fn(Worker\ExecuteRequest $request, Worker\ExecuteContext $context, Engine\ConnectorInterface $connector, Engine\Response\FactoryInterface $response, Engine\DispatcherInterface $dispatcher, LoggerInterface $logger): HttpResponseInterface => $response->ok([
    'foo' => 'baz',
]);
