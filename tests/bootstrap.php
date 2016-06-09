<?php

require(__DIR__ . '/../vendor/autoload.php');

PSX\Framework\Test\Environment::setup(__DIR__ . '/..', function ($fromSchema) {

    $version = new Fusio\Impl\Database\Version\Version030();
    $schema  = $version->getSchema();
    Fusio\Impl\Tests\TestSchema::appendSchema($schema);

    return $schema;

});
