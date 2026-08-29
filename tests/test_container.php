<?php

use Fusio\Impl\Tests\Framework\Api\TypeHub\TestPublisher;
use PSX\Api\TypeHub\PublisherInterface;
use PSX\Framework\Environment\IPResolver;
use PSX\Framework\Migration\DependencyFactoryFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->get(DependencyFactoryFactory::class)
        ->call('addPath', ['Fusio\\Impl\\Tests\\Migrations', __DIR__ . '/../tests']);

    $services->get(IPResolver::class)
        ->public();

    $services->set(TestPublisher::class)
        ->decorate(PublisherInterface::class)
        ->arg('$publisher', service('.inner'));

};
