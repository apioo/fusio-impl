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

namespace Fusio\Impl\Migrations;

use Fusio\Adapter;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Plan\Invoice;
use PSX\Api\Resource;

/**
 * DataBag
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DataBag
{
    private $data;

    public function __construct()
    {
        $this->data = [
            'fusio_user' => [],
            'fusio_action' => [],
            'fusio_app' => [],
            'fusio_audit' => [],
            'fusio_config' => [],
            'fusio_category' => [],
            'fusio_connection' => [],
            'fusio_cronjob' => [],
            'fusio_event' => [],
            'fusio_log' => [],
            'fusio_plan' => [],
            'fusio_plan_contract' => [],
            'fusio_plan_invoice' => [],
            'fusio_provider' => [],
            'fusio_rate' => [],
            'fusio_routes' => [],
            'fusio_schema' => [],
            'fusio_scope' => [],
            'fusio_transaction' => [],
            'fusio_app_code' => [],
            'fusio_app_scope' => [],
            'fusio_app_token' => [],
            'fusio_cronjob_error' => [],
            'fusio_event_subscription' => [],
            'fusio_event_trigger' => [],
            'fusio_event_response' => [],
            'fusio_log_error' => [],
            'fusio_plan_usage' => [],
            'fusio_rate_allocation' => [],
            'fusio_routes_method' => [],
            'fusio_routes_response' => [],
            'fusio_scope_routes' => [],
            'fusio_user_grant' => [],
            'fusio_user_scope' => [],
            'fusio_user_attribute' => [],
        ];
    }

    public function addRoutes(string $category, array $routes)
    {
        $prio = 0;

        $this->addCategory($category);
        $this->addScope($category, $category);

        foreach ($routes as $route => $config) {
            if ($category !== 'default') {
                $path = '/' . $category . $route;
            } else {
                $path = $route;
            }
            $this->addRoute($category, $prio, $path, SchemaApiController::class);

            foreach ($config as $methodName => $method) {
                /** @var Method $method */
                if (!$this->hasId('fusio_action', $method->getAction())) {
                    $actionName = $this->getActionName($method->getAction());
                    $this->addAction($category, $actionName, $method->getAction());
                } else {
                    $actionName = $method->getAction();
                }

                $parametersName = null;
                if ($method->getParameters()) {
                    if (!$this->hasId('fusio_schema', $method->getParameters())) {
                        $parametersName = $this->getSchemaName($method->getParameters());
                        $this->addSchema($category, $parametersName, $method->getParameters());
                    } else {
                        $parametersName = $method->getParameters();
                    }
                }

                $requestName = null;
                if ($method->getRequest()) {
                    if (!$this->hasId('fusio_schema', $method->getRequest())) {
                        $requestName = $this->getSchemaName($method->getRequest());
                        $this->addSchema($category, $requestName, $method->getRequest());
                    } else {
                        $requestName = $method->getRequest();
                    }
                }

                if ($method->getScope()) {
                    $this->addScope($category, $method->getScope());
                    $this->addScopeRoute($method->getScope(), $path);
                }

                if ($method->getEventName()) {
                    $this->addEvent($category, $method->getEventName());
                }

                $this->addRouteMethod(
                    $path,
                    $methodName,
                    $parametersName,
                    $requestName,
                    $actionName,
                    $method
                );

                foreach ($method->getResponses() as $code => $response) {
                    if (!$this->hasId('fusio_schema', $response)) {
                        $responseName = $this->getSchemaName($response);
                        $this->addSchema($category, $responseName, $response);
                    } else {
                        $responseName = $response;
                    }

                    $this->addRouteMethodResponse($path, $methodName, $code, $responseName);
                }
            }

            $prio++;
        }
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = array_values($value);
        }

        return $result;
    }

    public function addAction(string $category, string $name, string $class, ?string $config = null, ?string $date = null)
    {
        $this->data['fusio_action'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => 1,
            'name' => $name,
            'class' => $class,
            'engine' => PhpClass::class,
            'config' => $config,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addApp(string $user, string $name, string $url, string $appKey, string $appSecret, int $status = Table\App::STATUS_ACTIVE, ?string $date = null)
    {
        $this->data['fusio_app'][$name] = [
            'user_id' => $this->getId('fusio_user', $user),
            'status' => $status,
            'name' => $name,
            'url' => $url,
            'parameters' => '',
            'app_key' => $appKey,
            'app_secret' => $appSecret,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addAppCode(string $app, string $user, string $code, string $scope, ?string $date = null)
    {
        $this->data['fusio_app_code'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'code' => $code,
            'redirect_uri' => '',
            'scope' => $scope,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addAppScope(string $app, string $scope)
    {
        $this->data['fusio_app_scope'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addAppToken(string $app, string $user, string $token, string $refresh, string $scope, string $expire, ?string $date = null)
    {
        $this->data['fusio_app_token'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'status' => Table\App\Token::STATUS_ACTIVE,
            'token' => $token,
            'refresh' => $refresh,
            'scope' => $scope,
            'ip' => '127.0.0.1',
            'expire' => $expire,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addAudit(string $app, string $user, int $ref, string $event, string $message, ?string $date = null)
    {
        $this->data['fusio_audit'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'user_id' => $this->getId('fusio_user', $user),
            'ref_id' => $ref,
            'event' => $event,
            'ip' => '127.0.0.1',
            'message' => $message,
            'content' => null,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addCategory(string $category)
    {
        $this->data['fusio_category'][$category] = [
            'name' => $category,
        ];
    }

    public function addConfig(string $name, int $type, $value, string $description)
    {
        $this->data['fusio_config'][$name] = [
            'name' => $name,
            'type' => $type,
            'description' => $description,
            'value' => $value
        ];
    }

    public function addConnection(string $name, string $class, ?string $config = null)
    {
        $this->data['fusio_connection'][$name] = [
            'status' => Table\Connection::STATUS_ACTIVE,
            'name' => $name,
            'class' => $class,
            'config' => $config
        ];
    }

    public function addCronjob(string $name, string $cron, string $action)
    {
        $this->data['fusio_cronjob'][$name] = [
            'status' => Table\Cronjob::STATUS_ACTIVE,
            'name' => $name,
            'cron' => $cron,
            'action' => $action,
            'execute_date' => '2015-02-27 19:59:15',
            'exit_code' => 0
        ];
    }

    public function addCronjobError(string $cronjob, string $message)
    {
        $this->data['fusio_cronjob_error'][] = [
            'cronjob_id' => $this->getId('fusio_cronjob', $cronjob),
            'message' => $message,
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74
        ];
    }

    public function addEvent(string $category, string $name, string $description = '')
    {
        $this->data['fusio_event'][$name] = [
            'category_id' => $this->getId('fusio_category', $category),
            'status' => Table\Event::STATUS_ACTIVE,
            'name' => $name,
            'description' => $description
        ];
    }

    public function addEventResponse(int $trigger, int $subscription)
    {
        $this->data['fusio_event_response'][] = [
            'trigger_id' => $this->getId('fusio_event_trigger', $trigger),
            'subscription_id' => $this->getId('fusio_event_subscription', $subscription),
            'status' => 2,
            'code' => 200,
            'attempts' => 1,
            'execute_date' => '2018-06-02 14:41:23',
            'insert_date' => '2018-06-02 14:41:23'
        ];
    }

    public function addEventSubscription(string $event, string $user, string $endpoint)
    {
        $this->data['fusio_event_subscription'][] = [
            'event_id' => $this->getId('fusio_event', $event),
            'user_id' => $this->getId('fusio_user', $user),
            'status' => 1,
            'endpoint' => $endpoint
        ];
    }

    public function addEventTrigger(string $event, string $payload, ?string $date = null)
    {
        $this->data['fusio_event_trigger'][] = [
            'event_id' => $this->getId('fusio_event', $event),
            'status' => 2,
            'payload' => $payload,
            'insert_date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addLog(string $app, string $route)
    {
        $this->data['fusio_log'][] = [
            'app_id' => $this->getId('fusio_app', $app),
            'route_id' => $this->getId('fusio_routes', $route),
            'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'method' => 'GET',
            'path' => '/bar',
            'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'body' => 'foobar',
            'execution_time' => 500000,
            'date' => '2015-06-25 22:49:09'
        ];
    }

    public function addLogError(int $log)
    {
        $this->data['fusio_log_error'][] = [
            'log_id' => $this->getId('fusio_log', $log),
            'message' => 'Syntax error, malformed JSON',
            'trace' => '[trace]',
            'file' => '[file]',
            'line' => 74
        ];
    }

    public function addPlan(string $name, float $price, int $points, ?int $period)
    {
        $this->data['fusio_plan'][$name] = [
            'status' => Table\Plan::STATUS_ACTIVE,
            'name' => $name,
            'description' => '',
            'price' => $price,
            'points' => $points,
            'period_type' => $period
        ];
    }

    public function addPlanContract(string $user, string $plan, float $amount, int $points, int $period, ?string $date = null)
    {
        $this->data['fusio_plan_contract'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'plan_id' => $this->getId('fusio_plan', $plan),
            'status' => Table\Plan\Contract::STATUS_ACTIVE,
            'amount' => $amount,
            'points' => $points,
            'period_type' => $period,
            'insert_date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addPlanInvoice(int $contract, ?int $prevId, string $user, string $displayId, int $status, float $amount, int $points, string $fromDate, string $toDate, ?string $payDate, ?string $date = null)
    {
        $this->data['fusio_plan_invoice'][] = [
            'contract_id' => $this->getId('fusio_plan_contract', $contract),
            'prev_id' => $prevId,
            'user_id' => $this->getId('fusio_user', $user),
            'display_id' => $displayId,
            'status' => $status,
            'amount' => $amount,
            'points' => $points,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'pay_date' => $payDate,
            'insert_date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addPlanUsage(string $route, string $user, string $app, int $points, ?string $date = null)
    {
        $this->data['fusio_plan_usage'][] = [
            'route_id' => $this->getId('fusio_routes', $route),
            'user_id' => $this->getId('fusio_user', $user),
            'app_id' => $this->getId('fusio_app', $app),
            'points' => $points,
            'insert_date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addTransaction(int $invoice, string $provider, string $transactionId, string $remoteId, float $amount, string $returnUrl, ?string $date = null)
    {
        $this->data['fusio_transaction'][] = [
            'invoice_id' => $this->getId('fusio_plan_invoice', $invoice),
            'status' => 1,
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'remote_id' => $remoteId,
            'amount' => $amount,
            'return_url' => $returnUrl,
            'update_date' => null,
            'insert_date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date,
        ];
    }

    public function addRate(string $name, int $priority, int $rateLimit, string $timespan)
    {
        $this->data['fusio_rate'][$name] = [
            'status' => Table\Rate::STATUS_ACTIVE,
            'priority' => $priority,
            'name' => $name,
            'rate_limit' => $rateLimit,
            'timespan' => $timespan
        ];
    }

    public function addRateAllocation(string $rate, ?string $route = null, ?string $app = null, ?bool $authenticated = null, ?string $parameters = null)
    {
        $this->data['fusio_rate_allocation'][] = [
            'rate_id' => $this->getId('fusio_rate', $rate),
            'route_id' => $route !== null ? $this->getId('fusio_routes', $route) : null,
            'app_id' => $app !== null ? $this->getId('fusio_app', $app) : null,
            'authenticated' => $authenticated,
            'parameters' => $parameters
        ];
    }

    public function addRoute(string $category, int $prio, string $path, string $controller)
    {
        $this->data['fusio_routes'][$path] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => 1,
            'priority' => $prio,
            'methods' => 'ANY',
            'path' => $path,
            'controller' => $controller
        ];
    }

    public function addRouteMethod(string $path, string $methodName, ?string $parameters, ?string $request, string $action, Method $method)
    {
        $this->data['fusio_routes_method'][$path . $methodName] = [
            'route_id' => self::getId('fusio_routes', $path),
            'method' => $methodName,
            'version' => 1,
            'status' => $method->getStatus(),
            'active' => 1,
            'public' => $method->isPublic() ? 1 : 0,
            'operation_id' => $method->getOperationId(),
            'parameters' => $parameters,
            'request' => $request,
            'action' => $action,
            'costs' => $method->getCosts()
        ];
    }

    public function addRouteMethodResponse(string $path, string $methodName, string $code, string $response)
    {
        $this->data['fusio_routes_response'][$path . $methodName . $code] = [
            'method_id' => self::getId('fusio_routes_method', $path . $methodName),
            'code' => $code,
            'response' => $response
        ];
    }

    public function addSchema(string $category, string $name, string $source, ?string $form = null)
    {
        $this->data['fusio_schema'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'status' => 1,
            'name' => $name,
            'source' => $source,
            'form' => $form
        ];
    }

    public function addScope(string $category, string $name, string $description = '')
    {
        $this->data['fusio_scope'][$name] = [
            'category_id' => self::getId('fusio_category', $category),
            'name' => $name,
            'description' => $description
        ];
    }

    public function addScopeRoute(string $scope, string $path)
    {
        $this->data['fusio_scope_routes'][$scope . $path] = [
            'scope_id' => self::getId('fusio_scope', $scope),
            'route_id' => self::getId('fusio_routes', $path),
            'allow' => 1,
            'methods' => 'GET|POST|PUT|PATCH|DELETE'
        ];
    }

    public function addUser(string $name, string $email, string $password, ?int $points = null, int $status = Table\User::STATUS_ADMINISTRATOR, ?string $date = null)
    {
        $this->data['fusio_user'][$name] = [
            'status' => $status,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'points' => $points,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date
        ];
    }

    public function addUserScope(string $user, string $scope)
    {
        $this->data['fusio_user_scope'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'scope_id' => $this->getId('fusio_scope', $scope),
        ];
    }

    public function addUserGrant(string $user, string $app, bool $allow, ?string $date = null)
    {
        $this->data['fusio_user_grant'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'app_id' => $this->getId('fusio_app', $app),
            'allow' => $allow,
            'date' => $date === null ? (new \DateTime())->format('Y-m-d H:i:s') : $date
        ];
    }

    public function addUserAttribute(string $user, string $name, string $value)
    {
        $this->data['fusio_user_attribute'][] = [
            'user_id' => $this->getId('fusio_user', $user),
            'name' => $name,
            'value' => $value
        ];
    }

    public function addTable(string $table, array $rows)
    {
        if (isset($this->data[$table])) {
            throw new \RuntimeException('Table ' . $table . ' already exists');
        }

        $this->data[$table] = $rows;
    }

    public function getId(string $type, $name): int
    {
        if (!isset($this->data[$type])) {
            throw new \RuntimeException('Provided an invalid type ' . $type);
        }

        $index = 1;
        foreach ($this->data[$type] as $key => $value) {
            if ($name === $key) {
                return $index;
            }
            $index++;
        }

        throw new \RuntimeException('Could not find name ' . $name . ' for type ' . $type);
    }

    private function hasId(string $type, $name): bool
    {
        try {
            $this->getId($type, $name);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    private function getActionName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }

    private function getSchemaName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }
}