<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Console\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExecuteCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = Environment::getService('engine_dispatcher');

        // dispatch event
        $dispatcher->dispatch('foo-event', [
            'branch' => getenv('TRAVIS_BRANCH'),
            'build_id' => getenv('TRAVIS_BUILD_ID'),
            'build_number' => getenv('TRAVIS_BUILD_NUMBER'),
            'commit' => getenv('TRAVIS_COMMIT'),
            'commit_message' => getenv('TRAVIS_COMMIT_MESSAGE'),
            'event_type' => getenv('TRAVIS_EVENT_TYPE'),
            'job_id' => getenv('TRAVIS_JOB_ID'),
            'job_number' => getenv('TRAVIS_JOB_NUMBER'),
            'os_name' => getenv('TRAVIS_OS_NAME'),
            'repo_slug' => getenv('TRAVIS_REPO_SLUG'),
            'travis_tag' => getenv('TRAVIS_TAG'),
            'php_version' => getenv('TRAVIS_PHP_VERSION'),
        ]);

        $command = Environment::getService('console')->find('event:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertStringContainsString('Execution successful', $actual);

        $responses = $this->connection->fetchAll('SELECT trigger_id, subscription_id, status, code, attempts FROM fusio_event_response ORDER BY trigger_id ASC, subscription_id ASC');

        $this->assertEquals(3, count($responses));
        $this->assertEquals(1, $responses[0]['trigger_id']);
        $this->assertEquals(1, $responses[0]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[0]['status']);
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['attempts']);

        $this->assertEquals(2, $responses[1]['trigger_id']);
        $this->assertEquals(1, $responses[1]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[1]['status']);
        $this->assertEquals(200, $responses[1]['code']);
        $this->assertEquals(1, $responses[1]['attempts']);

        $this->assertEquals(2, $responses[2]['trigger_id']);
        $this->assertEquals(2, $responses[2]['subscription_id']);
        $this->assertEquals(Table\Event\Response::STATUS_DONE, $responses[2]['status']);
        $this->assertEquals(200, $responses[2]['code']);
        $this->assertEquals(1, $responses[2]['attempts']);
    }
}
