<?php

use Fusio\Worker;
use Fusio\Engine;
use Psr\Log\LoggerInterface;

return fn(Worker\ExecuteRequest $request, Worker\ExecuteContext $context, Engine\ConnectorInterface $connector, Engine\Response\FactoryInterface $response, Engine\DispatcherInterface $dispatcher, LoggerInterface $logger) => $response->ok([
    'foo' => 'bar',
]);
