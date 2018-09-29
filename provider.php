<?php

return [
    // Contains all action classes which are available at the backend. If a
    // class is registered here the user can select the action
    'action' => [
        \Fusio\Adapter\File\Action\FileProcessor::class,
        \Fusio\Adapter\Http\Action\HttpProcessor::class,
        \Fusio\Adapter\Php\Action\PhpProcessor::class,
        \Fusio\Adapter\Php\Action\PhpSandbox::class,
        \Fusio\Adapter\Sql\Action\SqlTable::class,
        \Fusio\Adapter\Util\Action\UtilStaticResponse::class,
        \Fusio\Adapter\V8\Action\V8Processor::class,
    ],
    // Contains all connection classes which are available at the backend. If a
    // class is registered here the user can select the action
    'connection' => [
        \Fusio\Adapter\Http\Connection\Http::class,
        \Fusio\Adapter\Sql\Connection\Sql::class,
        \Fusio\Adapter\Sql\Connection\SqlAdvanced::class,
    ],
    // Contains all available payment provider. Through a payment provider it is
    // possible to charge for points which can be required for specific routes
    'payment' => [
    ],
    // Contains all available user provider. Through a user provider a user can
    // authenticate with a remote provider 
    'user' => [
        \Fusio\Impl\Provider\User\Facebook::class,
        \Fusio\Impl\Provider\User\Github::class,
        \Fusio\Impl\Provider\User\Google::class,
    ],
];
