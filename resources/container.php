<?php

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
use Fusio\Impl\Controller\ActionExecutor;
use Fusio\Impl\Factory\Resolver;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Framework\Loader\LocationFinder\DatabaseFinder;
use Fusio\Impl\Framework\Loader\RoutingParser\CompositeParser;
use Fusio\Impl\Framework\Loader\RoutingParser\DatabaseParser;
use Fusio\Impl\Mail\SenderInterface as MailSenderInterface;
use Fusio\Impl\Provider;
use Fusio\Impl\Provider\ActionProvider;
use Fusio\Impl\Provider\ConnectionProvider;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Service\Action\Queue\Producer;
use Fusio\Impl\Webhook\SenderInterface as WebhookSenderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use PSX\Framework\Controller\ControllerInterface;
use PSX\Framework\Loader\ContextFactoryInterface;
use PSX\Framework\Loader\LocationFinderInterface;
use PSX\Framework\Loader\RoutingParser\CachedParser;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Http\Filter\UserAgentEnforcer;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);

    $services
        ->instanceof(ControllerInterface::class)
        ->tag('psx.controller');

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
    $services->load('Fusio\\Impl\\Backend\\Action\\', __DIR__ . '/../src/Backend/Action')
        ->public();

    $services->load('Fusio\\Impl\\Backend\\View\\', __DIR__ . '/../src/Backend/View')
        ->public();

    $services->load('Fusio\\Impl\\Consumer\\Action\\', __DIR__ . '/../src/Consumer/Action')
        ->public();

    $services->load('Fusio\\Impl\\Consumer\\View\\', __DIR__ . '/../src/Consumer/View')
        ->public();

    $services->load('Fusio\\Impl\\Service\\', __DIR__ . '/../src/Service')
        ->public();

    $services->load('Fusio\\Impl\\Controller\\', __DIR__ . '/../src/Controller')
        ->public();

    $services->load('Fusio\\Impl\\Table\\', __DIR__ . '/../src/Table')
        ->exclude('Generated')
        ->public();

    $services->load('Fusio\\Impl\\Mail\\Sender\\', __DIR__ . '/../src/Mail/Sender');
    $services->load('Fusio\\Impl\\Webhook\\Sender\\', __DIR__ . '/../src/Webhook/Sender');

    $services->set(Provider\ActionProvider::class);
    $services->set(Provider\ConnectionProvider::class);
    $services->set(Provider\GeneratorProvider::class);
    $services->set(Provider\PaymentProvider::class);
    $services->set(Provider\UserProvider::class);

    $services->set(ContextFactory::class);
    $services->alias(ContextFactoryInterface::class, ContextFactory::class);

    // psx
    $services->set(DatabaseParser::class);
    $services->set(CompositeParser::class);
    $services->set(CachedParser::class)
        ->args([
            service(CompositeParser::class),
            service(CacheItemPoolInterface::class),
            param('psx_debug'),
        ]);
    $services->alias(RoutingParserInterface::class, CachedParser::class);

    $services->set(DatabaseFinder::class);
    $services->alias(LocationFinderInterface::class, DatabaseFinder::class);

    $services->set(UserAgentEnforcer::class)
        ->public();

    $services->set(Psr16Cache::class);
    $services->alias(CacheInterface::class, Psr16Cache::class);

};