<?php

/*
This file contains classes which extend the functionality of Fusio. If you
register a new adapter and this adapter provides such a class, Fusio will
automatically add the class to this file. You can also manually add a new
class. The following list contains an explanation of each extension point:

- action
  Contains all action classes which are available at the backend. If a class is
  registered here the user can select this action. The class must implement the
  interface: Fusio\Engine\ActionInterface
- connection
  Contains all connection classes which are available at the backend. If a class
  is registered here the user can select this connection. The class must
  implement the interface: Fusio\Engine\ConnectionInterface
- payment
  Contains all available payment provider. Through a payment provider it is
  possible to charge for points which can be required for specific routes. The
  class must implement the interface: Fusio\Engine\Payment\ProviderInterface
- user
  Contains all available user provider. Through a user provider a user can
  authenticate with a remote provider i.e. Google. The class must implement the
  interface: Fusio\Engine\User\ProviderInterface
*/

return [
    'action' => [
        \Fusio\Adapter\File\Action\FileProcessor::class,
        \Fusio\Adapter\GraphQL\Action\GraphQLProcessor::class,
        \Fusio\Adapter\Http\Action\HttpProcessor::class,
        \Fusio\Adapter\Php\Action\PhpProcessor::class,
        \Fusio\Adapter\Php\Action\PhpSandbox::class,
        \Fusio\Adapter\Smtp\Action\SmtpSend::class,
        \Fusio\Adapter\Sql\Action\SqlSelectAll::class,
        \Fusio\Adapter\Sql\Action\SqlSelectRow::class,
        \Fusio\Adapter\Sql\Action\SqlInsert::class,
        \Fusio\Adapter\Sql\Action\SqlUpdate::class,
        \Fusio\Adapter\Sql\Action\SqlDelete::class,
        \Fusio\Adapter\Sql\Action\Query\SqlQueryAll::class,
        \Fusio\Adapter\Sql\Action\Query\SqlQueryRow::class,
        \Fusio\Adapter\Util\Action\UtilStaticResponse::class,
        \Fusio\Impl\Tests\Adapter\Test\VoidAction::class,
    ],
    'connection' => [
        \Fusio\Adapter\File\Connection\Ftp::class,
        \Fusio\Adapter\GraphQL\Connection\GraphQL::class,
        \Fusio\Adapter\Http\Connection\Http::class,
        \Fusio\Adapter\Smtp\Connection\Smtp::class,
        \Fusio\Adapter\Soap\Connection\Soap::class,
        \Fusio\Adapter\Sql\Connection\Sql::class,
        \Fusio\Adapter\Sql\Connection\SqlAdvanced::class,
        \Fusio\Impl\Tests\Adapter\Test\VoidConnection::class,
    ],
    'payment' => [
        \Fusio\Impl\Tests\Adapter\Test\Paypal::class,
    ],
    'user' => [
        \Fusio\Impl\Provider\User\Facebook::class,
        \Fusio\Impl\Provider\User\Github::class,
        \Fusio\Impl\Provider\User\Google::class,
    ],
    'routes' => [
        \Fusio\Impl\Tests\Service\Routes\TestProvider::class,
    ],
];

