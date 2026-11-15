<?php

use Fusio\Impl\Adapter\AdapterFinder;
use Fusio\Impl\Tests\Framework\Api\TypeHub\TestPublisher;
use PSX\Framework\Dependency\ContainerBuilder;

return ContainerBuilder::build(
    __DIR__,
    true,
    static function () {
        $configs = [
            __DIR__ . '/vendor/psx/framework/resources/container.php',
            __DIR__ . '/vendor/fusio/cli/resources/container.php',
            __DIR__ . '/vendor/fusio/engine/resources/container.php',
            __DIR__ . '/resources/container.php',
        ];

        if (class_exists(TestPublisher::class)) {
            $configs[] = __DIR__ . '/tests/test_container.php';
        }

        $configs = array_merge($configs, AdapterFinder::getFiles(__DIR__ . '/provider.php'));

        return $configs;
    },
);
