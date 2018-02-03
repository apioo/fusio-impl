<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Console\Action;

use Fusio\Impl\Service\Action\Executor;
use Fusio\Impl\Table;
use PSX\Data\Record\Transformer;
use PSX\Json\Parser;
use PSX\Sql\Condition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExecuteCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\Action\Executor
     */
    protected $executor;

    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @param \Fusio\Impl\Service\Action\Executor $executor
     * @param \Fusio\Impl\Table\Action $actionTable
     */
    public function __construct(Executor $executor, Table\Action $actionTable)
    {
        parent::__construct();

        $this->executor    = $executor;
        $this->actionTable = $actionTable;
    }

    protected function configure()
    {
        $this
            ->setName('action:execute')
            ->setDescription('Executes a specific action')
            ->addOption('method', 'm', InputOption::VALUE_REQUIRED, 'HTTP method')
            ->addOption('uriFragments', 'f', InputOption::VALUE_REQUIRED, 'Uri fragments as url encoded string "key=value"')
            ->addOption('parameters', 'p', InputOption::VALUE_REQUIRED, 'Query parameters as url encoded string "key=value"')
            ->addOption('headers', 'e', InputOption::VALUE_REQUIRED, 'Headers as url encoded string "key=value"')
            ->addOption('body', 'b', InputOption::VALUE_REQUIRED, 'Body as json input use "-" for stdin')
            ->addArgument('action', InputArgument::REQUIRED, 'Action name or id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $actionId = $this->getActionId($input->getArgument('action'));
        $body     = $this->getBody($input->getOption('body'));

        $response = $this->executor->execute(
            $actionId,
            $input->getOption('method') ?: 'GET',
            $input->getOption('uriFragments') ?: null,
            $input->getOption('parameters') ?: null,
            $input->getOption('headers') ?: null,
            $body
        );

        if ($response !== null) {
            $data = Transformer::toStdClass($response->getBody());
            $body = Parser::encode($data, JSON_PRETTY_PRINT);

            $output->writeln($body);
            return 0;
        } else {
            $output->writeln('Unknown action');
            return 1;
        }
    }
    
    private function getActionId($action)
    {
        if (is_numeric($action)) {
            $column = 'id';
        } else {
            $column = 'name';
        }

        $action = $this->actionTable->getOneBy(new Condition([$column, '=', $action]));
        if (!empty($action)) {
            return (int) $action['id'];
        } else {
            throw new \RuntimeException('Invalid action name or id');
        }
    }

    private function getBody($body)
    {
        if ($body !== null) {
            if ($body == '-') {
                $body = stream_get_contents(STDIN);
            }

            return Transformer::toRecord(Parser::decode($body));
        } else {
            return null;
        }
    }
}
