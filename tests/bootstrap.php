<?php

require(__DIR__ . '/../vendor/autoload.php');

$container = require_once __DIR__ . '/container.php';

/** @var \PSX\Framework\Test\Environment $environment */
$environment = $container->get(\PSX\Framework\Test\Environment::class);
$environment->setup(getConnectionParams());

function getConnectionParams(): array
{
    switch (getenv('DB')) {
        case 'mysql':
            return [
                'dbname'   => 'fusio',
                'user'     => 'root',
                'password' => 'test1234',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ];

        case 'postgres':
            return [
                'dbname'   => 'fusio',
                'user'     => 'postgres',
                'password' => 'postgres',
                'host'     => 'localhost',
                'driver'   => 'pdo_pgsql',
            ];

        default:
        case 'sqlite':
            return [
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ];
    }
}
