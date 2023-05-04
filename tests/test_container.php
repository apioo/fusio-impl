<?php

use Psr\Cache\CacheItemPoolInterface;
use PSX\Framework\Controller\ControllerInterface;
use PSX\Framework\Listener\PHPUnitExceptionListener;
use PSX\Framework\OAuth2\AuthorizerInterface;
use PSX\Framework\OAuth2\CallbackInterface;
use PSX\Framework\OAuth2\GrantTypeInterface;
use PSX\Framework\Tests\OAuth2\TestAuthorizer;
use PSX\Framework\Tests\OAuth2\TestCallback;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

};
