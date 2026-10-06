<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Command\Grid;

use Doctrine\DBAL\DriverManager;
use Fusio\Adapter;
use Fusio\Adapter\Sql\Generator\SqlDatabase;
use Fusio\Cli\Service\Authenticator;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\Model\UserInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Repository\UserRepository;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ConnectionConfig;
use Fusio\Model\Backend\ConnectionCreate;
use Fusio\Model\Backend\ConnectionUpdate;
use Fusio\Model\Backend\GeneratorProvider;
use Fusio\Model\Backend\GeneratorProviderConfig;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * SetupCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 *
 * @phpstan-type Params array{
 *     driver: ?string,
 *     host: ?string,
 *     user: ?string,
 *     password: ?string,
 *     dbname: ?string
 * }
 */
class SetupCommand extends Command
{
    use TypeSafeTrait;

    public function __construct(
        private readonly Service\Connection $connectionService,
        private readonly Service\Generator $generator,
        private readonly Table\Connection $connectionTable,
        private readonly Authenticator $authenticator,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('grid:setup')
            ->setDescription('Builds a complete REST API based on an existing database connection')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the connection')
            ->addOption('driver', 'd', InputOption::VALUE_OPTIONAL, 'Database driver (pdo_mysql, pdo_pgsql, pdo_sqlite)')
            ->addOption('host', 'H', InputOption::VALUE_OPTIONAL, 'Database host')
            ->addOption('port', 'P', InputOption::VALUE_OPTIONAL, 'Database port')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'Database user')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Database password')
            ->addOption('dbname', 'D', InputOption::VALUE_OPTIONAL, 'Database name')
            ->addOption('prefix', 't', InputOption::VALUE_OPTIONAL, 'Table prefix');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fusio Grid - Relational Database REST Setup');

        $userContext = $this->newUserContext();

        $driver = $input->getOption('driver');
        if (empty($driver) && $input->isInteractive()) {
            $choices = [
                'pdo_mysql' => 'MySQL / MariaDB',
                'pdo_pgsql' => 'PostgreSQL',
                'pdo_sqlite' => 'SQLite'
            ];

            $driver = $io->choice('Select database driver', $choices, 'pdo_mysql');
        }

        $host = $input->getOption('host');
        if (empty($host) && $driver !== 'pdo_sqlite' && $input->isInteractive()) {
            $host = $io->askQuestion(new Question('Database host', '127.0.0.1'));
        }

        $dbname = $input->getOption('dbname');
        if (empty($dbname) && $input->isInteractive()) {
            $dbname = $io->askQuestion(new Question('Database name'));
        }

        $user = $input->getOption('user');
        if (empty($user) && $driver !== 'pdo_sqlite' && $input->isInteractive()) {
            $user = $io->askQuestion(new Question('Database user'));
        }

        $password = $input->getOption('password');
        if (empty($password) && $driver !== 'pdo_sqlite' && $input->isInteractive()) {
            $passQuestion = new Question('Database password');
            $passQuestion->setHidden(true);

            $password = $io->askQuestion($passQuestion);
        }

        $prefix = $input->getOption('prefix');
        if ($input->isInteractive()) {
            $prefix = $io->askQuestion(new Question('Table prefix'));
        }

        $io->text("Connecting to target database <info>{$dbname}</info> ...");
        $params = $this->testConnection($driver, $host, $user, $password, $dbname);
        $io->success('Database connection successfully established');

        $name = $this->getConnectionName($dbname, $input, $io);
        $io->text("Setup connection <info>{$name}</info> ...");
        $connectionId = $this->ensureConnection($name, $params, $userContext);
        $io->success('Connection is available');

        $io->text("Building changelog ...");
        $config = $this->showChangelog($connectionId, $prefix, $io);

        if ($input->isInteractive()) {
            if (!$io->confirm('Do you want to execute?')) {
                $io->text('Canceled');
                return Command::FAILURE;
            }
        }

        $provider = new GeneratorProvider();
        $provider->setPath('/' . $name);
        $provider->setPublic(false);
        $provider->setConfig($config);
        $this->generator->create(SqlDatabase::class, $provider, $userContext);

        $io->success('Successfully generated REST API');

        return self::SUCCESS;
    }

    /**
     * @param Params $params
     */
    private function ensureConnection(string $name, array $params, UserContext $context): int
    {
        $config = ConnectionConfig::fromArray([
            'database' => $params['dbname'] ?? null,
            'username' => $params['user'] ?? null,
            'password' => $params['password'] ?? null,
            'host' => $params['host'] ?? null,
            'type' => $params['driver'] ?? null,
        ]);

        $row = $this->connectionTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $name);
        if ($row instanceof Table\Generated\ConnectionRow) {
            $connection = new ConnectionUpdate();
            $connection->setName($name);
            $connection->setClass(ClassName::serialize(Adapter\Sql\Connection\Sql::class));
            $connection->setConfig($config);

            return $this->connectionService->update('' . $row->getId(), $connection, $context);
        } else {
            $connection = new ConnectionCreate();
            $connection->setName($name);
            $connection->setClass(ClassName::serialize(Adapter\Sql\Connection\Sql::class));
            $connection->setConfig($config);

            return $this->connectionService->create($connection, $context);
        }
    }

    private function newUserContext(): UserContext
    {
        $whoami = $this->authenticator->whoami();
        $id = $whoami->id ?? null;
        if (empty($id)) {
            throw new RuntimeException('Could not get current user id, please run the login command to authenticate');
        }

        $user = $this->userRepository->get($id);
        if (!$user instanceof UserInterface) {
            throw new RuntimeException('Could not find authenticated user with id ' . $id);
        }

        return UserContext::newContext($user->getCategoryId(), $user->getId());
    }

    /**
     * @return Params
     */
    private function testConnection(?string $driver, ?string $host, ?string $user, ?string $password, ?string $dbname): array
    {
        $params = array_filter([
            'driver'   => $driver,
            'host'     => $host,
            'user'     => $user,
            'password' => $password,
            'dbname'   => $dbname,
        ]);

        $targetConnection = DriverManager::getConnection($params);

        $schemaManager = $targetConnection->createSchemaManager();
        if (count($schemaManager->listTables()) === 0) {
            throw new RuntimeException('It looks like the database has no tables');
        }

        return $params;
    }

    private function getConnectionName(?string $dbname, InputInterface $input, SymfonyStyle $io): string
    {
        $defaultName = !empty($dbname) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', strtolower($dbname)) : null;

        $name = $input->getArgument('name');
        if (empty($name)) {
            if ($input->isInteractive()) {
                $name = $io->askQuestion(new Question('Enter connection name', $defaultName));
            } else {
                $name = $defaultName;
            }
        }

        if (empty($name)) {
            throw new RuntimeException(
                'Could not determine connection name. Please specify a connection name argument or pass the --dbname option.'
            );
        }

        return $name;
    }

    private function showChangelog(int $connectionId, ?string $prefix, SymfonyStyle $io): GeneratorProviderConfig
    {
        $config = GeneratorProviderConfig::fromArray([
            'connection' => $connectionId,
            'prefix' => $prefix,
        ]);

        $changelog = $this->generator->getChangelog(SqlDatabase::class, $config);

        $operations = $changelog['operations'] ?? [];

        $table = $io->createTable();
        $table->setHeaders(['Name', 'Method', 'Path']);

        foreach ($operations as $operation) {
            $table->addRow([$operation->getName(), $operation->getHttpMethod(), $operation->getHttpPath()]);
        }

        $table->render();

        return $config;
    }
}
