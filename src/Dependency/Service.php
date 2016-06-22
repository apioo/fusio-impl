<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Dependency;

use Fusio\Impl;

/**
 * Service
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait Service
{
    /**
     * @return \Fusio\Impl\Service\User
     */
    public function getUserService()
    {
        return new Impl\Service\User(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\User'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\User\Scope')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes
     */
    public function getRoutesService()
    {
        return new Impl\Service\Routes(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope\Route'),
            $this->get('routes_deploy_service'),
            $this->get('routes_relation_service')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes\Method
     */
    public function getRoutesMethodService()
    {
        return new Impl\Service\Routes\Method(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method'),
            $this->get('schema_loader')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Action
     */
    public function getActionService()
    {
        return new Impl\Service\Action(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Action'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Action'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App
     */
    public function getAppService()
    {
        return new Impl\Service\App(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App\Token'),
            $this->get('config')->get('fusio_project_key')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Code
     */
    public function getAppCodeService()
    {
        return new Impl\Service\App\Code(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App\Code')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Developer
     */
    public function getAppDeveloperService()
    {
        return new Impl\Service\App\Developer(
            $this->get('app_service'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\User\Scope'),
            $this->get('config')->get('fusio_app_per_consumer'),
            $this->get('config')->get('fusio_app_approval')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Grant
     */
    public function getAppGrantService()
    {
        return new Impl\Service\App\Grant(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\User\Grant'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App\Token')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Config
     */
    public function getConfigService()
    {
        return new Impl\Service\Config(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Config')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Connection
     */
    public function getConnectionService()
    {
        return new Impl\Service\Connection(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Connection'),
            $this->get('connection_parser'),
            $this->get('config')->get('fusio_project_key')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Dashboard
     */
    public function getDashboardService()
    {
        return new Impl\Service\Dashboard(
            $this->get('connection')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Log
     */
    public function getLogService()
    {
        return new Impl\Service\Log(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Log')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Statistic
     */
    public function getStatisticService()
    {
        return new Impl\Service\Statistic(
            $this->get('connection')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Import
     */
    public function getImportService()
    {
        return new Impl\Service\System\Import(
            $this->get('dispatch'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Export
     */
    public function getExportService()
    {
        return new Impl\Service\System\Export(
            $this->get('dispatch'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Schema
     */
    public function getSchemaService()
    {
        return new Impl\Service\Schema(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Schema'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Schema'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method'),
            $this->get('schema_parser')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Scope
     */
    public function getScopeService()
    {
        return new Impl\Service\Scope(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Scope\Route'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\App\Scope'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\User\Scope')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes\Relation
     */
    public function getRoutesRelationService()
    {
        return new Impl\Service\Routes\Relation(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Schema'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Action'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Action'),
            $this->get('action_parser')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes\Deploy
     */
    public function getRoutesDeployService()
    {
        return new Impl\Service\Routes\Deploy(
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Routes\Method'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Schema'),
            $this->get('table_manager')->getTable('Fusio\Impl\Table\Action'),
            $this->get('action_parser')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Consumer
     */
    public function getConsumer()
    {
        return new Impl\Service\Consumer(
            $this->get('user_service'),
            $this->get('app_service'),
            $this->get('http_client'),
            $this->get('mailer'),
            $this->get('config')
        );
    }
}
