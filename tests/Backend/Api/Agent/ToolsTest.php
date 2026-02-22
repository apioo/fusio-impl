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

namespace Fusio\Impl\Tests\Backend\Api\Agent;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\DbTestCase;

/**
 * ToolsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ToolsTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/agent/tools', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "tools": [
        {
            "name": "authorization_getWhoami",
            "description": "Returns user data of the current authenticated user"
        },
        {
            "name": "authorization_revoke",
            "description": "Revoke the access token of the current authenticated user"
        },
        {
            "name": "backend_account_changePassword",
            "description": "Changes the password of the authenticated user"
        },
        {
            "name": "backend_account_get",
            "description": "Returns user data of the authenticated user"
        },
        {
            "name": "backend_account_update",
            "description": "Updates user data of the authenticated user"
        },
        {
            "name": "backend_action_create",
            "description": "Creates a new action"
        },
        {
            "name": "backend_action_delete",
            "description": "Deletes an existing action"
        },
        {
            "name": "backend_action_execute",
            "description": "Executes a specific action. This method should be used to test an action configuration"
        },
        {
            "name": "backend_action_get",
            "description": "Returns a specific action"
        },
        {
            "name": "backend_action_getAll",
            "description": "Returns a paginated list of actions"
        },
        {
            "name": "backend_action_getClasses",
            "description": "Returns all available action classes"
        },
        {
            "name": "backend_action_getCommits",
            "description": "Returns a paginated list of action commits"
        },
        {
            "name": "backend_action_getForm",
            "description": "Returns the action config form"
        },
        {
            "name": "backend_action_update",
            "description": "Updates an existing action"
        },
        {
            "name": "backend_agent_create",
            "description": "Creates a new agent"
        },
        {
            "name": "backend_agent_delete",
            "description": "Deletes an existing agent"
        },
        {
            "name": "backend_agent_get",
            "description": "Returns a specific agent"
        },
        {
            "name": "backend_agent_getAll",
            "description": "Returns a paginated list of agents"
        },
        {
            "name": "backend_agent_getTools",
            "description": "Returns available tools for an agent"
        },
        {
            "name": "backend_agent_message_getAll",
            "description": "Returns a paginated list of agent messages"
        },
        {
            "name": "backend_agent_message_submit",
            "description": "Submits a new agent message"
        },
        {
            "name": "backend_agent_update",
            "description": "Updates an existing agent"
        },
        {
            "name": "backend_app_create",
            "description": "Creates a new app"
        },
        {
            "name": "backend_app_delete",
            "description": "Deletes an existing app"
        },
        {
            "name": "backend_app_deleteToken",
            "description": "Deletes an existing token from an app"
        },
        {
            "name": "backend_app_get",
            "description": "Returns a specific app"
        },
        {
            "name": "backend_app_getAll",
            "description": "Returns a paginated list of apps"
        },
        {
            "name": "backend_app_update",
            "description": "Updates an existing app"
        },
        {
            "name": "backend_audit_get",
            "description": "Returns a specific audit"
        },
        {
            "name": "backend_audit_getAll",
            "description": "Returns a paginated list of audits"
        },
        {
            "name": "backend_backup_export",
            "description": "Generates an backup of the current system"
        },
        {
            "name": "backend_backup_import",
            "description": "Imports an backup to the current system"
        },
        {
            "name": "backend_bundle_create",
            "description": "Creates a new bundle"
        },
        {
            "name": "backend_bundle_delete",
            "description": "Deletes an existing bundle"
        },
        {
            "name": "backend_bundle_get",
            "description": "Returns a specific bundle"
        },
        {
            "name": "backend_bundle_getAll",
            "description": "Returns a paginated list of bundles"
        },
        {
            "name": "backend_bundle_publish",
            "description": "Publish an existing bundle to the marketplace"
        },
        {
            "name": "backend_bundle_update",
            "description": "Updates an existing bundle"
        },
        {
            "name": "backend_category_create",
            "description": "Creates a new category"
        },
        {
            "name": "backend_category_delete",
            "description": "Deletes an existing category"
        },
        {
            "name": "backend_category_get",
            "description": "Returns a specific category"
        },
        {
            "name": "backend_category_getAll",
            "description": "Returns a paginated list of categories"
        },
        {
            "name": "backend_category_update",
            "description": "Updates an existing category"
        },
        {
            "name": "backend_config_get",
            "description": "Returns a specific config"
        },
        {
            "name": "backend_config_getAll",
            "description": "Returns a paginated list of configuration values"
        },
        {
            "name": "backend_config_update",
            "description": "Updates an existing config value"
        },
        {
            "name": "backend_connection_agent_send",
            "description": "Sends a message to an agent"
        },
        {
            "name": "backend_connection_create",
            "description": "Creates a new connection"
        },
        {
            "name": "backend_connection_database_createRow",
            "description": "Creates a new row at a table on a database"
        },
        {
            "name": "backend_connection_database_createTable",
            "description": "Creates a new table on a database"
        },
        {
            "name": "backend_connection_database_deleteRow",
            "description": "Deletes an existing row at a table on a database"
        },
        {
            "name": "backend_connection_database_deleteTable",
            "description": "Deletes an existing table on a database"
        },
        {
            "name": "backend_connection_database_getRow",
            "description": "Returns a specific row at a table on a database"
        },
        {
            "name": "backend_connection_database_getRows",
            "description": "Returns paginated rows at a table on a database"
        },
        {
            "name": "backend_connection_database_getTable",
            "description": "Returns the schema of a specific table on a database"
        },
        {
            "name": "backend_connection_database_getTables",
            "description": "Returns all available tables on a database"
        },
        {
            "name": "backend_connection_database_updateRow",
            "description": "Updates an existing row at a table on a database"
        },
        {
            "name": "backend_connection_database_updateTable",
            "description": "Updates an existing table on a database"
        },
        {
            "name": "backend_connection_delete",
            "description": "Deletes an existing connection"
        },
        {
            "name": "backend_connection_filesystem_delete",
            "description": "Deletes an existing file on the filesystem connection"
        },
        {
            "name": "backend_connection_filesystem_get",
            "description": "Returns the content of the provided file id on the filesystem connection"
        },
        {
            "name": "backend_connection_filesystem_getAll",
            "description": "Returns all available files on the filesystem connection"
        },
        {
            "name": "backend_connection_get",
            "description": "Returns a specific connection"
        },
        {
            "name": "backend_connection_getAll",
            "description": "Returns a paginated list of connections"
        },
        {
            "name": "backend_connection_getClasses",
            "description": "Returns all available connection classes"
        },
        {
            "name": "backend_connection_getForm",
            "description": "Returns the connection config form"
        },
        {
            "name": "backend_connection_getRedirect",
            "description": "Returns a redirect url to start the OAuth2 authorization flow for the given connection"
        },
        {
            "name": "backend_connection_http_execute",
            "description": "Sends an arbitrary HTTP request to the connection"
        },
        {
            "name": "backend_connection_sdk_get",
            "description": "Returns the SDK specification"
        },
        {
            "name": "backend_connection_update",
            "description": "Updates an existing connection"
        },
        {
            "name": "backend_cronjob_create",
            "description": "Creates a new cronjob"
        },
        {
            "name": "backend_cronjob_delete",
            "description": "Deletes an existing cronjob"
        },
        {
            "name": "backend_cronjob_get",
            "description": "Returns a specific cronjob"
        },
        {
            "name": "backend_cronjob_getAll",
            "description": "Returns a paginated list of cronjobs"
        },
        {
            "name": "backend_cronjob_update",
            "description": "Updates an existing cronjob"
        },
        {
            "name": "backend_dashboard_getAll",
            "description": "Returns all available dashboard widgets"
        },
        {
            "name": "backend_event_create",
            "description": "Creates a new event"
        },
        {
            "name": "backend_event_delete",
            "description": "Deletes an existing event"
        },
        {
            "name": "backend_event_get",
            "description": "Returns a specific event"
        },
        {
            "name": "backend_event_getAll",
            "description": "Returns a paginated list of events"
        },
        {
            "name": "backend_event_update",
            "description": "Updates an existing event"
        },
        {
            "name": "backend_firewall_create",
            "description": "Creates a new firewall rule"
        },
        {
            "name": "backend_firewall_delete",
            "description": "Deletes an existing firewall rule"
        },
        {
            "name": "backend_firewall_get",
            "description": "Returns a specific firewall rule"
        },
        {
            "name": "backend_firewall_getAll",
            "description": "Returns a paginated list of firewall rules"
        },
        {
            "name": "backend_firewall_update",
            "description": "Updates an existing firewall rule"
        },
        {
            "name": "backend_form_create",
            "description": "Creates a new form"
        },
        {
            "name": "backend_form_delete",
            "description": "Deletes an existing form"
        },
        {
            "name": "backend_form_get",
            "description": "Returns a specific form"
        },
        {
            "name": "backend_form_getAll",
            "description": "Returns a paginated list of forms"
        },
        {
            "name": "backend_form_update",
            "description": "Updates an existing form"
        },
        {
            "name": "backend_generator_executeProvider",
            "description": "Executes a generator with the provided config"
        },
        {
            "name": "backend_generator_getChangelog",
            "description": "Generates a changelog of all potential changes if you execute this generator with the provided config"
        },
        {
            "name": "backend_generator_getClasses",
            "description": "Returns all available generator classes"
        },
        {
            "name": "backend_generator_getForm",
            "description": "Returns the generator config form"
        },
        {
            "name": "backend_identity_create",
            "description": "Creates a new identity"
        },
        {
            "name": "backend_identity_delete",
            "description": "Deletes an existing identity"
        },
        {
            "name": "backend_identity_get",
            "description": "Returns a specific identity"
        },
        {
            "name": "backend_identity_getAll",
            "description": "Returns a paginated list of identities"
        },
        {
            "name": "backend_identity_getClasses",
            "description": "Returns all available identity classes"
        },
        {
            "name": "backend_identity_getForm",
            "description": "Returns the identity config form"
        },
        {
            "name": "backend_identity_update",
            "description": "Updates an existing identity"
        },
        {
            "name": "backend_log_get",
            "description": "Returns a specific log"
        },
        {
            "name": "backend_log_getAll",
            "description": "Returns a paginated list of logs"
        },
        {
            "name": "backend_log_getAllErrors",
            "description": "Returns a paginated list of log errors"
        },
        {
            "name": "backend_log_getError",
            "description": "Returns a specific error"
        },
        {
            "name": "backend_marketplace_action_get",
            "description": "Returns a specific marketplace action"
        },
        {
            "name": "backend_marketplace_action_getAll",
            "description": "Returns a paginated list of marketplace actions"
        },
        {
            "name": "backend_marketplace_action_install",
            "description": "Installs an action from the marketplace"
        },
        {
            "name": "backend_marketplace_action_upgrade",
            "description": "Upgrades an action from the marketplace"
        },
        {
            "name": "backend_marketplace_app_get",
            "description": "Returns a specific marketplace app"
        },
        {
            "name": "backend_marketplace_app_getAll",
            "description": "Returns a paginated list of marketplace apps"
        },
        {
            "name": "backend_marketplace_app_install",
            "description": "Installs an app from the marketplace"
        },
        {
            "name": "backend_marketplace_app_upgrade",
            "description": "Upgrades an app from the marketplace"
        },
        {
            "name": "backend_marketplace_bundle_get",
            "description": "Returns a specific marketplace bundle"
        },
        {
            "name": "backend_marketplace_bundle_getAll",
            "description": "Returns a paginated list of marketplace bundles"
        },
        {
            "name": "backend_marketplace_bundle_install",
            "description": "Installs an bundle from the marketplace"
        },
        {
            "name": "backend_marketplace_bundle_upgrade",
            "description": "Upgrades an bundle from the marketplace"
        },
        {
            "name": "backend_operation_create",
            "description": "Creates a new operation"
        },
        {
            "name": "backend_operation_delete",
            "description": "Deletes an existing operation"
        },
        {
            "name": "backend_operation_get",
            "description": "Returns a specific operation"
        },
        {
            "name": "backend_operation_getAll",
            "description": "Returns a paginated list of operations"
        },
        {
            "name": "backend_operation_update",
            "description": "Updates an existing operation"
        },
        {
            "name": "backend_page_create",
            "description": "Creates a new page"
        },
        {
            "name": "backend_page_delete",
            "description": "Deletes an existing page"
        },
        {
            "name": "backend_page_get",
            "description": "Returns a specific page"
        },
        {
            "name": "backend_page_getAll",
            "description": "Returns a paginated list of pages"
        },
        {
            "name": "backend_page_update",
            "description": "Updates an existing page"
        },
        {
            "name": "backend_plan_create",
            "description": "Creates a new plan"
        },
        {
            "name": "backend_plan_delete",
            "description": "Deletes an existing plan"
        },
        {
            "name": "backend_plan_get",
            "description": "Returns a specific plan"
        },
        {
            "name": "backend_plan_getAll",
            "description": "Returns a paginated list of plans"
        },
        {
            "name": "backend_plan_update",
            "description": "Updates an existing plan"
        },
        {
            "name": "backend_rate_create",
            "description": "Creates a new rate limitation"
        },
        {
            "name": "backend_rate_delete",
            "description": "Deletes an existing rate"
        },
        {
            "name": "backend_rate_get",
            "description": "Returns a specific rate"
        },
        {
            "name": "backend_rate_getAll",
            "description": "Returns a paginated list of rate limitations"
        },
        {
            "name": "backend_rate_update",
            "description": "Updates an existing rate"
        },
        {
            "name": "backend_role_create",
            "description": "Creates a new role"
        },
        {
            "name": "backend_role_delete",
            "description": "Deletes an existing role"
        },
        {
            "name": "backend_role_get",
            "description": "Returns a specific role"
        },
        {
            "name": "backend_role_getAll",
            "description": "Returns a paginated list of roles"
        },
        {
            "name": "backend_role_update",
            "description": "Updates an existing role"
        },
        {
            "name": "backend_schema_create",
            "description": "Creates a new schema"
        },
        {
            "name": "backend_schema_delete",
            "description": "Deletes an existing schema"
        },
        {
            "name": "backend_schema_get",
            "description": "Returns a specific schema"
        },
        {
            "name": "backend_schema_getAll",
            "description": "Returns a paginated list of schemas"
        },
        {
            "name": "backend_schema_getCommits",
            "description": "Returns a paginated list of schema commits"
        },
        {
            "name": "backend_schema_getPreview",
            "description": "Returns a HTML preview of the provided schema"
        },
        {
            "name": "backend_schema_update",
            "description": "Updates an existing schema"
        },
        {
            "name": "backend_schema_updateForm",
            "description": "Updates an existing schema form"
        },
        {
            "name": "backend_scope_create",
            "description": "Creates a new scope"
        },
        {
            "name": "backend_scope_delete",
            "description": "Deletes an existing scope"
        },
        {
            "name": "backend_scope_get",
            "description": "Returns a specific scope"
        },
        {
            "name": "backend_scope_getAll",
            "description": "Returns a paginated list of scopes"
        },
        {
            "name": "backend_scope_getCategories",
            "description": "Returns all available scopes grouped by category"
        },
        {
            "name": "backend_scope_update",
            "description": "Updates an existing scope"
        },
        {
            "name": "backend_sdk_generate",
            "description": "Generates a specific SDK"
        },
        {
            "name": "backend_sdk_getAll",
            "description": "Returns a paginated list of SDKs"
        },
        {
            "name": "backend_statistic_getActivitiesPerUser",
            "description": "Returns a statistic containing the activities per user"
        },
        {
            "name": "backend_statistic_getCountRequests",
            "description": "Returns a statistic containing the request count"
        },
        {
            "name": "backend_statistic_getErrorsPerOperation",
            "description": "Returns a statistic containing the errors per operation"
        },
        {
            "name": "backend_statistic_getIncomingRequests",
            "description": "Returns a statistic containing the incoming requests"
        },
        {
            "name": "backend_statistic_getIncomingTransactions",
            "description": "Returns a statistic containing the incoming transactions"
        },
        {
            "name": "backend_statistic_getIssuedTokens",
            "description": "Returns a statistic containing the issues tokens"
        },
        {
            "name": "backend_statistic_getMostUsedActivities",
            "description": "Returns a statistic containing the most used activities"
        },
        {
            "name": "backend_statistic_getMostUsedApps",
            "description": "Returns a statistic containing the most used apps"
        },
        {
            "name": "backend_statistic_getMostUsedOperations",
            "description": "Returns a statistic containing the most used operations"
        },
        {
            "name": "backend_statistic_getRequestsPerIP",
            "description": "Returns a statistic containing the requests per ip"
        },
        {
            "name": "backend_statistic_getRequestsPerOperation",
            "description": "Returns a statistic containing the requests per operation"
        },
        {
            "name": "backend_statistic_getRequestsPerUser",
            "description": "Returns a statistic containing the requests per user"
        },
        {
            "name": "backend_statistic_getTestCoverage",
            "description": "Returns a statistic containing the test coverage"
        },
        {
            "name": "backend_statistic_getTimeAverage",
            "description": "Returns a statistic containing the time average"
        },
        {
            "name": "backend_statistic_getTimePerOperation",
            "description": "Returns a statistic containing the time per operation"
        },
        {
            "name": "backend_statistic_getUsedPoints",
            "description": "Returns a statistic containing the used points"
        },
        {
            "name": "backend_statistic_getUserRegistrations",
            "description": "Returns a statistic containing the user registrations"
        },
        {
            "name": "backend_taxonomy_create",
            "description": "Creates a new taxonomy"
        },
        {
            "name": "backend_taxonomy_delete",
            "description": "Deletes an existing taxonomy"
        },
        {
            "name": "backend_taxonomy_get",
            "description": "Returns a specific taxonomy"
        },
        {
            "name": "backend_taxonomy_getAll",
            "description": "Returns a paginated list of taxonomies"
        },
        {
            "name": "backend_taxonomy_move",
            "description": "Moves the provided ids to the taxonomy"
        },
        {
            "name": "backend_taxonomy_update",
            "description": "Updates an existing taxonomy"
        },
        {
            "name": "backend_tenant_remove",
            "description": "Removes an existing tenant"
        },
        {
            "name": "backend_tenant_setup",
            "description": "Setup a new tenant"
        },
        {
            "name": "backend_test_get",
            "description": "Returns a specific test"
        },
        {
            "name": "backend_test_getAll",
            "description": "Returns a paginated list of tests"
        },
        {
            "name": "backend_test_refresh",
            "description": "Refresh all tests"
        },
        {
            "name": "backend_test_run",
            "description": "Run all tests"
        },
        {
            "name": "backend_test_update",
            "description": "Updates an existing test"
        },
        {
            "name": "backend_token_get",
            "description": "Returns a specific token"
        },
        {
            "name": "backend_token_getAll",
            "description": "Returns a paginated list of tokens"
        },
        {
            "name": "backend_transaction_get",
            "description": "Returns a specific transaction"
        },
        {
            "name": "backend_transaction_getAll",
            "description": "Returns a paginated list of transactions"
        },
        {
            "name": "backend_trash_getAllByType",
            "description": "Returns all deleted records by trash type"
        },
        {
            "name": "backend_trash_getTypes",
            "description": "Returns all trash types"
        },
        {
            "name": "backend_trash_restore",
            "description": "Restores a previously deleted record"
        },
        {
            "name": "backend_trigger_create",
            "description": "Creates a new trigger"
        },
        {
            "name": "backend_trigger_delete",
            "description": "Deletes an existing trigger"
        },
        {
            "name": "backend_trigger_get",
            "description": "Returns a specific trigger"
        },
        {
            "name": "backend_trigger_getAll",
            "description": "Returns a paginated list of triggers"
        },
        {
            "name": "backend_trigger_update",
            "description": "Updates an existing trigger"
        },
        {
            "name": "backend_user_create",
            "description": "Creates a new user"
        },
        {
            "name": "backend_user_delete",
            "description": "Deletes an existing user"
        },
        {
            "name": "backend_user_get",
            "description": "Returns a specific user"
        },
        {
            "name": "backend_user_getAll",
            "description": "Returns a paginated list of users"
        },
        {
            "name": "backend_user_resend",
            "description": "Resend the activation mail to the provided user"
        },
        {
            "name": "backend_user_update",
            "description": "Updates an existing user"
        },
        {
            "name": "backend_webhook_create",
            "description": "Creates a new webhook"
        },
        {
            "name": "backend_webhook_delete",
            "description": "Deletes an existing webhook"
        },
        {
            "name": "backend_webhook_get",
            "description": "Returns a specific webhook"
        },
        {
            "name": "backend_webhook_getAll",
            "description": "Returns a paginated list of webhooks"
        },
        {
            "name": "backend_webhook_update",
            "description": "Updates an existing webhook"
        },
        {
            "name": "consumer_account_activate",
            "description": "Activates an previously registered account through a token which was provided to the user via email"
        },
        {
            "name": "consumer_account_authorize",
            "description": "Authorizes the access of a specific app for the authenticated user"
        },
        {
            "name": "consumer_account_changePassword",
            "description": "Change the password for the authenticated user"
        },
        {
            "name": "consumer_account_executePasswordReset",
            "description": "Change the password after the password reset flow was started"
        },
        {
            "name": "consumer_account_get",
            "description": "Returns a user data for the authenticated user"
        },
        {
            "name": "consumer_account_getApp",
            "description": "Returns information about a specific app to start the OAuth2 authorization code flow"
        },
        {
            "name": "consumer_account_login",
            "description": "User login by providing a username and password"
        },
        {
            "name": "consumer_account_refresh",
            "description": "Refresh a previously obtained access token"
        },
        {
            "name": "consumer_account_register",
            "description": "Register a new user account"
        },
        {
            "name": "consumer_account_requestPasswordReset",
            "description": "Start the password reset flow"
        },
        {
            "name": "consumer_account_update",
            "description": "Updates user data for the authenticated user"
        },
        {
            "name": "consumer_app_create",
            "description": "Creates a new app for the authenticated user"
        },
        {
            "name": "consumer_app_delete",
            "description": "Deletes an existing app for the authenticated user"
        },
        {
            "name": "consumer_app_get",
            "description": "Returns a specific app for the authenticated user"
        },
        {
            "name": "consumer_app_getAll",
            "description": "Returns a paginated list of apps which are assigned to the authenticated user"
        },
        {
            "name": "consumer_app_update",
            "description": "Updates an existing app for the authenticated user"
        },
        {
            "name": "consumer_event_get",
            "description": "Returns a specific event for the authenticated user"
        },
        {
            "name": "consumer_event_getAll",
            "description": "Returns a paginated list of apps which are assigned to the authenticated user"
        },
        {
            "name": "consumer_form_get",
            "description": "Returns a specific form for the authenticated user"
        },
        {
            "name": "consumer_form_getAll",
            "description": "Returns a paginated list of forms which are relevant to the authenticated user"
        },
        {
            "name": "consumer_grant_delete",
            "description": "Deletes an existing grant for an app which was created by the authenticated user"
        },
        {
            "name": "consumer_grant_getAll",
            "description": "Returns a paginated list of grants which are assigned to the authenticated user"
        },
        {
            "name": "consumer_identity_exchange",
            "description": "Identity callback endpoint to exchange an access token"
        },
        {
            "name": "consumer_identity_getAll",
            "description": "Returns a paginated list of identities which are relevant to the authenticated user"
        },
        {
            "name": "consumer_identity_redirect",
            "description": "Redirect the user to the configured identity provider"
        },
        {
            "name": "consumer_log_get",
            "description": "Returns a specific log for the authenticated user"
        },
        {
            "name": "consumer_log_getAll",
            "description": "Returns a paginated list of logs which are assigned to the authenticated user"
        },
        {
            "name": "consumer_page_get",
            "description": "Returns a specific page for the authenticated user"
        },
        {
            "name": "consumer_page_getAll",
            "description": "Returns a paginated list of pages which are relevant to the authenticated user"
        },
        {
            "name": "consumer_payment_checkout",
            "description": "Start the checkout process for a specific plan"
        },
        {
            "name": "consumer_payment_portal",
            "description": "Generates a payment portal link for the authenticated user"
        },
        {
            "name": "consumer_plan_get",
            "description": "Returns a specific plan for the authenticated user"
        },
        {
            "name": "consumer_plan_getAll",
            "description": "Returns a paginated list of plans which are relevant to the authenticated user"
        },
        {
            "name": "consumer_scope_getAll",
            "description": "Returns a paginated list of scopes which are assigned to the authenticated user"
        },
        {
            "name": "consumer_scope_getCategories",
            "description": "Returns all scopes by category"
        },
        {
            "name": "consumer_token_create",
            "description": "Creates a new token for the authenticated user"
        },
        {
            "name": "consumer_token_delete",
            "description": "Deletes an existing token for the authenticated user"
        },
        {
            "name": "consumer_token_get",
            "description": "Returns a specific token for the authenticated user"
        },
        {
            "name": "consumer_token_getAll",
            "description": "Returns a paginated list of tokens which are assigned to the authenticated user"
        },
        {
            "name": "consumer_token_update",
            "description": "Updates an existing token for the authenticated user"
        },
        {
            "name": "consumer_transaction_get",
            "description": "Returns a specific transaction for the authenticated user"
        },
        {
            "name": "consumer_transaction_getAll",
            "description": "Returns a paginated list of transactions which are assigned to the authenticated user"
        },
        {
            "name": "consumer_webhook_create",
            "description": "Creates a new webhook for the authenticated user"
        },
        {
            "name": "consumer_webhook_delete",
            "description": "Deletes an existing webhook for the authenticated user"
        },
        {
            "name": "consumer_webhook_get",
            "description": "Returns a specific webhook for the authenticated user"
        },
        {
            "name": "consumer_webhook_getAll",
            "description": "Returns a paginated list of webhooks which are assigned to the authenticated user"
        },
        {
            "name": "consumer_webhook_update",
            "description": "Updates an existing webhook for the authenticated user"
        },
        {
            "name": "meta_getAbout",
            "description": "Returns meta information and links about the current installed Fusio version"
        },
        {
            "name": "system_connection_callback",
            "description": "Connection OAuth2 callback to authorize a connection"
        },
        {
            "name": "system_meta_getAbout",
            "description": "Returns meta information and links about the current installed Fusio version"
        },
        {
            "name": "system_meta_getDebug",
            "description": "Debug endpoint which returns the provided data"
        },
        {
            "name": "system_meta_getHealth",
            "description": "Health check endpoint which returns information about the health status of the system"
        },
        {
            "name": "system_meta_getRoutes",
            "description": "Returns all available routes"
        },
        {
            "name": "system_meta_getSchema",
            "description": "Returns details of a specific schema"
        },
        {
            "name": "system_payment_webhook",
            "description": "Payment webhook endpoint after successful purchase of a plan"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
