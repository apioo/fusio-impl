<?php

require(__DIR__ . '/../vendor/autoload.php');

define('FUSIO_IN_TEST', true);

PSX\Framework\Test\Environment::setup(__DIR__ . '/..');

runMigrations();

function runMigrations()
{
    $configuration = \Fusio\Impl\Migrations\ConfigurationBuilder::fromSystem(
        \PSX\Framework\Test\Environment::getService('connection')
    );

    $factory = new \Doctrine\Migrations\DependencyFactory($configuration);
    $factory->getMigrator()->migrate();
}