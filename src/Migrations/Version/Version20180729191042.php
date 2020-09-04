<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180729191042 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $appTable = $schema->getTable('fusio_app');

        $this->skipIf($appTable->hasColumn('user_id'), 'Column names already migrated');
        $this->skipIf($this->platform->getName() !== 'mysql', 'Can rename column only on mysql');

        $this->addSql('ALTER TABLE fusio_app CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_app CHANGE appKey app_key varchar(255)');
        $this->addSql('ALTER TABLE fusio_app CHANGE appSecret app_secret varchar(255)');

        $this->addSql('ALTER TABLE fusio_app_scope CHANGE appId app_id int(11)');
        $this->addSql('ALTER TABLE fusio_app_scope CHANGE scopeId scope_id int(11)');

        $this->addSql('ALTER TABLE fusio_app_token CHANGE appId app_id int(11)');
        $this->addSql('ALTER TABLE fusio_app_token CHANGE userId user_id int(11)');

        $this->addSql('ALTER TABLE fusio_app_code CHANGE appId app_id int(11)');
        $this->addSql('ALTER TABLE fusio_app_code CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_app_code CHANGE redirectUri redirect_uri varchar(255)');

        $this->addSql('ALTER TABLE fusio_audit CHANGE appId app_id int(11)');
        $this->addSql('ALTER TABLE fusio_audit CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_audit CHANGE refId ref_id int(11)');

        $this->addSql('ALTER TABLE fusio_cronjob CHANGE executeDate execute_date datetime');
        $this->addSql('ALTER TABLE fusio_cronjob CHANGE exitCode exit_code int(11)');

        $this->addSql('ALTER TABLE fusio_cronjob_error CHANGE cronjobId cronjob_id int(11)');

        $this->addSql('ALTER TABLE fusio_event_response CHANGE triggerId trigger_id int(11)');
        $this->addSql('ALTER TABLE fusio_event_response CHANGE subscriptionId subscription_id int(11)');
        $this->addSql('ALTER TABLE fusio_event_response CHANGE executeDate execute_date int(11)');
        $this->addSql('ALTER TABLE fusio_event_response CHANGE insertDate insert_date int(11)');

        $this->addSql('ALTER TABLE fusio_event_subscription CHANGE eventId event_id int(11)');
        $this->addSql('ALTER TABLE fusio_event_subscription CHANGE userId user_id int(11)');

        $this->addSql('ALTER TABLE fusio_event_trigger CHANGE eventId event_id int(11)');
        $this->addSql('ALTER TABLE fusio_event_trigger CHANGE insertDate insert_date datetime');

        $this->addSql('ALTER TABLE fusio_log CHANGE routeId route_id int(11)');
        $this->addSql('ALTER TABLE fusio_log CHANGE appId app_id int(11)');
        $this->addSql('ALTER TABLE fusio_log CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_log CHANGE userAgent user_agent varchar(255)');
        $this->addSql('ALTER TABLE fusio_log CHANGE executionTime execution_time int(11)');

        $this->addSql('ALTER TABLE fusio_log_error CHANGE logId log_id int(11)');

        $this->addSql('ALTER TABLE fusio_routes_method CHANGE routeId route_id int(11)');
        $this->addSql('ALTER TABLE fusio_routes_method CHANGE schemaCache schema_cache longtext');
        $this->addSql('ALTER TABLE fusio_routes_method CHANGE actionCache action_cache longtext');

        $this->addSql('ALTER TABLE fusio_routes_response CHANGE methodId method_id int(11)');

        $this->addSql('ALTER TABLE fusio_rate CHANGE rateLimit rate_limit int(11)');

        $this->addSql('ALTER TABLE fusio_rate_allocation CHANGE rateId rate_id int(11)');
        $this->addSql('ALTER TABLE fusio_rate_allocation CHANGE routeId route_id int(11)');
        $this->addSql('ALTER TABLE fusio_rate_allocation CHANGE appId app_id int(11)');

        $this->addSql('ALTER TABLE fusio_user CHANGE remoteId remote_id int(11)');

        $this->addSql('ALTER TABLE fusio_scope_routes CHANGE scopeId scope_id int(11)');
        $this->addSql('ALTER TABLE fusio_scope_routes CHANGE routeId route_id int(11)');

        $this->addSql('ALTER TABLE fusio_user_grant CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_user_grant CHANGE appId app_id int(11)');

        $this->addSql('ALTER TABLE fusio_user_scope CHANGE userId user_id int(11)');
        $this->addSql('ALTER TABLE fusio_user_scope CHANGE scopeId scope_id int(11)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
