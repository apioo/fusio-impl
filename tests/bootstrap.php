<?php

require __DIR__ . '/../vendor/autoload.php';

global $container;
$container = require_once __DIR__ . '/../container.php';

/** @var \PSX\Framework\Test\Environment $environment */
global $environment;
$environment = $container->get(\PSX\Framework\Test\Environment::class);
$environment->setup();
