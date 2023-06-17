<?php

use Fusio\Cli;
use Fusio\Engine\Action;
use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\Adapter\ServiceBuilder;
use Fusio\Engine\Connector;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Dispatcher;
use Fusio\Engine\DispatcherInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Form;
use Fusio\Engine\Parser;
use Fusio\Engine\Processor;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\Response;
use Fusio\Impl\Cli\Config;
use Fusio\Impl\Cli\Transport;
use Fusio\Impl\Factory\Resolver;
use Fusio\Impl\Framework;
use Fusio\Impl\Mail\SenderInterface as MailSenderInterface;
use Fusio\Impl\Provider;
use Fusio\Impl\Provider\ActionProvider;
use Fusio\Impl\Provider\ConnectionProvider;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Service\Action\Queue\Producer;
use Fusio\Impl\Webhook\SenderInterface as WebhookSenderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use PSX\Api;
use PSX\Framework\Dependency\Configurator;
use PSX\Framework\Filter\ControllerExecutorFactoryInterface;
use PSX\Framework\Loader\ContextFactoryInterface;
use PSX\Framework\Loader\ControllerResolverInterface;
use PSX\Framework\Loader\RoutingParser\CachedParser;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Framework\Migration\DependencyFactoryFactory;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Schema;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services = Configurator::services($services);

    $services
        ->instanceof(MailSenderInterface::class)
        ->tag('fusio.mailer.sender');

    $services
        ->instanceof(WebhookSenderInterface::class)
        ->tag('fusio.webhook.sender');

    // engine
    $services->set(ImplRepository\ActionDatabase::class);
    $services->alias(Repository\ActionInterface::class, ImplRepository\ActionDatabase::class);

    $services->set(ImplRepository\ConnectionDatabase::class);
    $services->alias(Repository\ConnectionInterface::class, ImplRepository\ConnectionDatabase::class);

    $services->set(ImplRepository\AppDatabase::class);
    $services->alias(Repository\AppInterface::class, ImplRepository\AppDatabase::class);

    $services->set(ImplRepository\UserDatabase::class);
    $services->alias(Repository\UserInterface::class, ImplRepository\UserDatabase::class);

    $services->set(Form\ElementFactory::class);
    $services->alias(Form\ElementFactoryInterface::class, Form\ElementFactory::class);

    $services->set(Factory\Resolver\PhpClass::class);
    $services->set(Resolver\HttpUrl::class);
    $services->set(Resolver\PhpFile::class);
    $services->set(Resolver\StaticFile::class);
    $services->set(Factory\Action::class)
        ->call('addResolver', [service(Factory\Resolver\PhpClass::class)])
        ->call('addResolver', [service(Resolver\HttpUrl::class)])
        ->call('addResolver', [service(Resolver\PhpFile::class)])
        ->call('addResolver', [service(Resolver\StaticFile::class)]);
    $services->alias(Factory\ActionInterface::class, Factory\Action::class);

    $services->set(Factory\Connection::class);
    $services->alias(Factory\ConnectionInterface::class, Factory\Connection::class);

    $services->set(ActionProvider::class);
    $services->alias(Parser\ActionInterface::class, ActionProvider::class);

    $services->set(ConnectionProvider::class);
    $services->alias(Parser\ConnectionInterface::class, ConnectionProvider::class);

    $services->set(Producer::class);
    $services->alias(Action\QueueInterface::class, Producer::class);

    $services->set(Processor::class);
    $services->alias(ProcessorInterface::class, Processor::class);

    $services->set(Dispatcher::class);
    $services->alias(DispatcherInterface::class, Dispatcher::class);

    $services->set(Connector::class);
    $services->alias(ConnectorInterface::class, Connector::class);

    $services->set(Response\Factory::class);
    $services->alias(Response\FactoryInterface::class, Response\Factory::class);

    $services->set(Action\Runtime::class);
    $services->alias(RuntimeInterface::class, Action\Runtime::class);

    // impl
    $services->load('Fusio\\Impl\\Authorization\\Action\\', __DIR__ . '/../src/Authorization/Action');
    $services->load('Fusio\\Impl\\Backend\\Action\\', __DIR__ . '/../src/Backend/Action');
    $services->load('Fusio\\Impl\\Backend\\View\\', __DIR__ . '/../src/Backend/View');
    $services->load('Fusio\\Impl\\Consumer\\Action\\', __DIR__ . '/../src/Consumer/Action');
    $services->load('Fusio\\Impl\\Consumer\\View\\', __DIR__ . '/../src/Consumer/View');
    $services->load('Fusio\\Impl\\Consumer\\Action\\', __DIR__ . '/../src/Consumer/Action');
    $services->load('Fusio\\Impl\\System\\Action\\', __DIR__ . '/../src/System/Action');
    $services->load('Fusio\\Impl\\Command\\', __DIR__ . '/../src/Command');
    $services->load('Fusio\\Impl\\Service\\', __DIR__ . '/../src/Service')
        ->public();
    $services->load('Fusio\\Impl\\Controller\\', __DIR__ . '/../src/Controller')
        ->public();
    $services->load('Fusio\\Impl\\Table\\', __DIR__ . '/../src/Table')
        ->exclude('Generated')
        ->public();
    $services->load('Fusio\\Impl\\Mail\\Sender\\', __DIR__ . '/../src/Mail/Sender');
    $services->load('Fusio\\Impl\\Webhook\\Sender\\', __DIR__ . '/../src/Webhook/Sender');
    $services->load('Fusio\\Impl\\Connection\\', __DIR__ . '/../src/Connection');
    $services->load('Fusio\\Impl\\Provider\\User\\', __DIR__ . '/../src/Provider/User')
        ->public();
    $services->load('Fusio\\Impl\\Provider\\Generator\\', __DIR__ . '/../src/Provider/Generator')
        ->public();
    $services->load('Fusio\\Impl\\Authorization\\GrantType\\', __DIR__ . '/../src/Authorization/GrantType')
        ->public();

    $services->set(Provider\ActionProvider::class);
    $services->set(Provider\ConnectionProvider::class);
    $services->set(Provider\GeneratorProvider::class);
    $services->set(Provider\PaymentProvider::class);
    $services->set(Provider\UserProvider::class);

    $services->set(Framework\Loader\ContextFactory::class);
    $services->alias(ContextFactoryInterface::class, Framework\Loader\ContextFactory::class);

    // cli
    $container->import(Cli\Adapter::getContainerFile());

    $services->set(Config::class);
    $services->alias(Cli\Config\ConfigInterface::class, Config::class);

    $services->set(Transport::class);
    $services->alias(Cli\Transport\TransportInterface::class, Transport::class);

    // psx
    $services->set(Framework\Loader\RoutingParser\DatabaseParser::class);
    $services->set(Framework\Loader\RoutingParser\CompositeParser::class);
    $services->set(CachedParser::class)
        ->args([
            service(Framework\Loader\RoutingParser\CompositeParser::class),
            service(CacheItemPoolInterface::class),
            param('psx_debug'),
        ]);
    $services->alias(RoutingParserInterface::class, CachedParser::class);

    $services->set(UserAgentEnforcer::class)
        ->public();

    $services->set(Psr16Cache::class);
    $services->alias(CacheInterface::class, Psr16Cache::class);

    $services->set(Framework\Filter\ActionExecutorFactory::class);
    $services->set(Framework\Filter\CompositeExecutorFactory::class);
    $services->alias(ControllerExecutorFactoryInterface::class, Framework\Filter\CompositeExecutorFactory::class);

    $services->set(Framework\Loader\ControllerResolver::class);
    $services->alias(ControllerResolverInterface::class, Framework\Loader\ControllerResolver::class);

    $services->set(Framework\Api\Scanner\FilterFactory::class);
    $services->alias(Api\Scanner\FilterFactoryInterface::class, Framework\Api\Scanner\FilterFactory::class);

    $services->set(Framework\Api\Configurator\OpenAPI::class)
        ->arg('$url', param('psx_url'))
        ->arg('$dispatch', param('psx_dispatch'));

    $services->set(Framework\Schema\Parser\Schema::class);
    $services->get(Schema\SchemaManager::class)
        ->call('register', ['schema', service(Framework\Schema\Parser\Schema::class)]);

    $services->set(Framework\Api\Parser\Operation::class);
    $services->get(Api\ApiManager::class)
        ->call('register', ['operation', service(Framework\Api\Parser\Operation::class)]);

    $services->get(DependencyFactoryFactory::class)
        ->call('addPath', ['Fusio\\Impl\\Migrations', __DIR__ . '/../src']);

};
