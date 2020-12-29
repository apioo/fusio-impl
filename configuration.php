<?php

return array(

    // OAuth2 access token expiration settings. How long can you use an access
    // token and the refresh token. After the expiration a user either need to
    // use a refresh token to extend the token or request a new token
    'fusio_expire_token'      => 'P2D',
    'fusio_expire_refresh'    => 'P3D',

    // The secret key of a project. It is recommended to change this to another
    // random value. This is used i.e. to encrypt the connection credentials in 
    // the database. NOTE IF YOU CHANGE THE KEY FUSIO CAN NO LONGER READ ANY 
    // DATA WHICH WAS ENCRYPTED BEFORE. BECAUSE OF THAT IT IS RECOMMENDED TO 
    // CHANGE THE KEY ONLY BEFORE THE INSTALLATION
    'fusio_project_key'       => '42eec18ffdbffc9fda6110dcc705d6ce',

    // Indicates whether the PHP sandbox feature is enabled. If yes it is
    // possible to create an action at the backend which contains PHP code. This
    // helps to quickly develop new actions but you should also be aware of the
    // security implications. The code gets checked by a parser which prevents
    // the use of unsafe functions but there is no guarantee that this is
    // complete safe. If you dont need this feature it is recommended to turn it
    // off, then it is not possible to create or update such actions
    'fusio_php_sandbox'       => true,

    // The three-character ISO-4217 currency code which is used to process
    // payments
    'fusio_payment_currency'  => 'EUR',

    // Points to the Fusio provider file which contains specific classes for the
    // system. Please take a look at the provider file for more information
    'fusio_provider'          => __DIR__ . '/provider.php',

    // A list of additional user attributes. Through this your app can easily
    // store additional attributes to the account
    'fusio_user_attributes'   => [
        'first_name',
        'last_name',
    ],

    // Settings of the internal mailer. By default we use the internal PHP mail
    // function
    /*
    'fusio_mailer'            => [
        'transport'           => 'smtp',
        'host'                => 'email-smtp.us-east-1.amazonaws.com',
        'port'                => 587,
        'username'            => 'my-username',
        'password'            => 'my-password',
        'encryption'          => 'tls',
    ],
    */

    // Endpoint of the apps repository. All listed apps can be installed by the
    // user at the backend app
    'fusio_marketplace_url'   => 'http://www.fusio-project.org/marketplace.yaml',

    // The public url to the apps folder (i.e. http://acme.com/apps or 
    // http://apps.acme.com)
    'fusio_apps_url'          => 'http://127.0.0.1/apps',

    // Location where the apps are persisted from the marketplace. By default
    // this is the public dir to access the apps directly, but it is also
    // possible to specify a different folder
    'fusio_apps_dir'          => __DIR__ . '/apps',

    // Location of the automatically generated cron file. Note Fusio writes only
    // to this file if it exists. In order to use the cronjob service you need
    // to create this file with i.e. "touch /etc/cron.d/fusio"
    'fusio_cron_file'         => '/etc/cron.d/fusio',

    // Command to execute the Fusio console which is used in the generated cron
    // file
    'fusio_cron_exec'         => '/usr/bin/php ' . __DIR__ . '/bin/fusio',

    // The web server type, based on this type Fusio generates the fitting
    // configuration format
    'fusio_server_type'       => null,

    // Location of the automatically generated web server config file. Note
    // Fusio writes only to this file if it exists. Also you may need to restart
    // the web server so that the config changes take affect
    'fusio_server_conf'       => null,

    // The url to the psx public folder (i.e. http://127.0.0.1/psx/public or 
    // http://localhost.com)
    'psx_url'                 => 'http://127.0.0.1',

    // The input path 'index.php/' or '' if you use mod_rewrite
    'psx_dispatch'            => '',

    // The default timezone
    'psx_timezone'            => 'UTC',

    // Whether PSX runs in debug mode or not. If not error reporting is set to 0
    // Also several caches are used if the debug mode is false
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

    // Global middleware which are applied before and after every request. Must
    // bei either a classname, closure or PSX\Dispatch\FilterInterface instance
    //'psx_filter_pre'          => [],
    //'psx_filter_post'         => [],

    // A closure which returns a doctrine cache implementation. If null the
    // filesystem cache is used
    //'psx_cache_factory'       => null,

    // A closure which returns a monolog handler implementation. If null the
    // system handler is used
    //'psx_logger_factory'      => null,

    // Class name of the error controller
    //'psx_error_controller'    => null,

    // If you only want to change the appearance of the error page you can 
    // specify a custom template
    //'psx_error_template'      => null,

);

function getConnectionParams($db)
{
    switch ($db) {
        case 'mysql':
            return [
                'dbname'   => 'fusio',
                'user'     => 'root',
                'password' => '',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ];
            break;

        case 'pgsql':
            return [
                'dbname'   => 'fusio',
                'user'     => 'postgres',
                'password' => '',
                'host'     => 'localhost',
                'driver'   => 'pdo_pgsql',
            ];
            break;

        default:
        case 'sqlite':
            return [
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ];
            break;
    }
}
