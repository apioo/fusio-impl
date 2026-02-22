<?php

use Fusio\Engine\Adapter\ServiceBuilder;
use Fusio\Impl\Tests\Adapter\Test\AgentConnection;
use Fusio\Impl\Tests\Adapter\Test\InspectAction;
use Fusio\Impl\Tests\Adapter\Test\MimeAction;
use Fusio\Impl\Tests\Adapter\Test\Paypal;
use Fusio\Impl\Tests\Adapter\Test\PaypalConnection;
use Fusio\Impl\Tests\Adapter\Test\VoidAction;
use Fusio\Impl\Tests\Adapter\Test\VoidConnection;
use Fusio\Impl\Tests\Service\Generator\TestProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services->set(AgentConnection::class);
    $services->set(InspectAction::class);
    $services->set(MimeAction::class);
    $services->set(Paypal::class);
    $services->set(PaypalConnection::class);
    $services->set(VoidAction::class);
    $services->set(VoidConnection::class);
    $services->set(TestProvider::class);
};
