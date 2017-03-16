<?php

require(__DIR__ . '/../vendor/autoload.php');

PSX\Framework\Test\Environment::setup(__DIR__ . '/..', function ($fromSchema) {
    $version = \Fusio\Impl\Database\Installer::getLatestVersion();
    $schema  = $version->getSchema();
    Fusio\Impl\Tests\TestSchema::appendSchema($schema);

    return $schema;
});
