<?php

return array(

    // This array contains a list of worker endpoints which can be used by Fusio to execute action code in different
    // programming languages. For more information please take a look at our worker documentation:
    // https://www.fusio-project.org/documentation/worker
    /*
    'fusio_worker'            => [
        'java'                => 'localhost:9090',
        'javascript'          => 'localhost:9091',
        'php'                 => 'localhost:9092',
        'python'              => 'localhost:9093',
    ],
    */

    // OAuth2 access token expiration settings. How long can you use an access token and the refresh token. After the
    // expiration a user either need to use a refresh token to extend the token or request a new token
    'fusio_expire_token'      => 'P2D',
    'fusio_expire_refresh'    => 'P3D',

    // The secret key of a project. It is recommended to change this to another random value. This is used i.e. to
    // encrypt the connection credentials in the database. NOTE IF YOU CHANGE THE KEY FUSIO CAN NO LONGER READ ANY DATA
    // WHICH WAS ENCRYPTED BEFORE. BECAUSE OF THAT IT IS RECOMMENDED TO CHANGE THE KEY ONLY BEFORE THE INSTALLATION
    'fusio_project_key'       => '42eec18ffdbffc9fda6110dcc705d6ce',

    // Indicates whether the PHP sandbox feature is enabled. If yes it is possible to use the PHP-Sandbox action which
    // executes PHP code directly on the server. The code gets checked by a parser which prevents the use of unsafe
    // functions but there is no guarantee that this is complete safe. Otherwise you can also use the PHP worker which
    // executes the code at the worker.
    'fusio_php_sandbox'       => true,

    // Points to the Fusio provider file which contains specific classes for the system. Please take a look at the
    // provider file for more information
    'fusio_provider'          => __DIR__ . '/provider.php',

    // A list of additional user attributes. Through this your app can easily store additional attributes to the account
    'fusio_user_attributes'   => [
        'first_name',
        'last_name',
    ],

    // Settings of the internal mailer. More information s.
    // https://symfony.com/doc/current/mailer.html#using-built-in-transports
    'fusio_mailer'            => 'native://default',

    // Describes the default email which Fusio uses as from address
    'fusio_mail_sender'       => null,

    // Indicates whether the marketplace is enabled. If yes it is possible to download and install other apps through
    // the backend
    'fusio_marketplace'       => false,

    // Endpoint of the apps repository. All listed apps can be installed by the user at the backend app
    'fusio_marketplace_url'   => 'https://www.fusio-project.org/marketplace.yaml',

    // The public url to the apps folder (i.e. http://acme.com/apps or http://apps.acme.com)
    'fusio_apps_url'          => 'http://127.0.0.1/apps',

    // Location where the apps are persisted from the marketplace. By default this is the public dir to access the apps
    // directly, but it is also possible to specify a different folder
    'fusio_apps_dir'          => __DIR__ . '/apps',

    // The url to the psx public folder (i.e. http://127.0.0.1/psx/public or http://localhost.com)
    'psx_url'                 => 'http://127.0.0.1',

    // The input path 'index.php/' or '' if you use mod_rewrite
    'psx_dispatch'            => '',

    // The default timezone
    'psx_timezone'            => 'UTC',

    // Whether PSX runs in debug mode or not. If not error reporting is set to 0 also several caches are used if the
    // debug mode is false
    'psx_debug'               => true,

    // Database parameters which are used for the doctrine DBAL connection
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
    'psx_connection'          => getConnectionParams(getenv('DB')),

    // Folder locations
    'psx_path_cache'          => __DIR__ . '/cache',
    'psx_path_public'         => __DIR__ . '/public',
    'psx_path_src'            => __DIR__ . '/src',

    // Supported writers
    'psx_supported_writer'    => [
        \PSX\Data\Writer\Json::class,
        \PSX\Data\Writer\Jsonp::class,
        \PSX\Data\Writer\Jsonx::class,
    ],

    // Global middleware which are applied before and after every request. Must bei either a classname, closure or
    // PSX\Http\FilterInterface instance
    //'psx_filter_pre'          => [],
    //'psx_filter_post'         => [],

    // A closure which returns a doctrine cache implementation. If null the filesystem cache is used
    //'psx_cache_factory'       => null,

    // Specify a specific log level
    //'psx_log_level' => \Monolog\Logger::ERROR,

    // A closure which returns a monolog handler implementation. If null the system handler is used
    //'psx_logger_factory'      => null,

);

function getConnectionParams($db)
{
    switch ($db) {
        case 'mysql':
            return [
                'dbname'   => 'fusio',
                'user'     => 'root',
                'password' => 'test1234',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ];

        case 'postgres':
            return [
                'dbname'   => 'fusio',
                'user'     => 'postgres',
                'password' => 'postgres',
                'host'     => 'localhost',
                'driver'   => 'pdo_pgsql',
            ];

        default:
        case 'sqlite':
            return [
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ];
    }
}
