<?php

use PSX\Framework\Environment\IPResolver;
use PSX\Framework\Migration\DependencyFactoryFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->get(DependencyFactoryFactory::class)
        ->call('addPath', ['Fusio\\Impl\\Tests\\Migrations', __DIR__ . '/../tests']);

    $services->get(IPResolver::class)
        ->public();

};
