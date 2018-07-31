<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180729191042 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $appTable = $schema->getTable('fusio_app');

        $this->skipIf($appTable->hasColumn('user_id'), 'Column names already migrated');
        $this->skipIf($this->platform->getName() !== 'mysql', 'Can rename column only on mysql');

        $this->addSql('ALTER TABLE fusio_app RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_app RENAME COLUMN appKey TO app_key');
        $this->addSql('ALTER TABLE fusio_app RENAME COLUMN appSecret TO app_secret');
        
        $this->addSql('ALTER TABLE fusio_app_scope RENAME COLUMN appId TO app_id');
        $this->addSql('ALTER TABLE fusio_app_scope RENAME COLUMN scopeId TO scope_id');
        
        $this->addSql('ALTER TABLE fusio_app_token RENAME COLUMN appId TO app_id');
        $this->addSql('ALTER TABLE fusio_app_token RENAME COLUMN userId TO user_id');
        
        $this->addSql('ALTER TABLE fusio_app_code RENAME COLUMN appId TO app_id');
        $this->addSql('ALTER TABLE fusio_app_code RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_app_code RENAME COLUMN redirectUri TO redirect_uri');
        
        $this->addSql('ALTER TABLE fusio_audit RENAME COLUMN appId TO app_id');
        $this->addSql('ALTER TABLE fusio_audit RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_audit RENAME COLUMN refId TO ref_id');
        
        $this->addSql('ALTER TABLE fusio_cronjob RENAME COLUMN executeDate TO execute_date');
        $this->addSql('ALTER TABLE fusio_cronjob RENAME COLUMN exitCode TO exit_code');
        
        $this->addSql('ALTER TABLE fusio_cronjob_error RENAME COLUMN cronjobId TO cronjob_id');
        
        $this->addSql('ALTER TABLE fusio_event_response RENAME COLUMN triggerId TO trigger_id');
        $this->addSql('ALTER TABLE fusio_event_response RENAME COLUMN subscriptionId TO subscription_id');
        $this->addSql('ALTER TABLE fusio_event_response RENAME COLUMN executeDate TO execute_date');
        $this->addSql('ALTER TABLE fusio_event_response RENAME COLUMN insertDate TO insert_date');
        
        $this->addSql('ALTER TABLE fusio_event_subscription RENAME COLUMN eventId TO event_id');
        $this->addSql('ALTER TABLE fusio_event_subscription RENAME COLUMN userId TO user_id');
        
        $this->addSql('ALTER TABLE fusio_event_trigger RENAME COLUMN eventId TO event_id');
        $this->addSql('ALTER TABLE fusio_event_trigger RENAME COLUMN insertDate TO insert_date');
        
        $this->addSql('ALTER TABLE fusio_log RENAME COLUMN routeId TO route_id');
        $this->addSql('ALTER TABLE fusio_log RENAME COLUMN appId TO app_id');
        $this->addSql('ALTER TABLE fusio_log RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_log RENAME COLUMN userAgent TO user_agent');
        $this->addSql('ALTER TABLE fusio_log RENAME COLUMN executionTime TO execution_time');
        
        $this->addSql('ALTER TABLE fusio_log_error RENAME COLUMN logId TO log_id');
        
        $this->addSql('ALTER TABLE fusio_routes_method RENAME COLUMN routeId TO route_id');
        $this->addSql('ALTER TABLE fusio_routes_method RENAME COLUMN schemaCache TO schema_cache');
        $this->addSql('ALTER TABLE fusio_routes_method RENAME COLUMN actionCache TO action_cache');
        
        $this->addSql('ALTER TABLE fusio_routes_response RENAME COLUMN methodId TO method_id');
        
        $this->addSql('ALTER TABLE fusio_rate RENAME COLUMN rateLimit TO rate_limit');
        
        $this->addSql('ALTER TABLE fusio_rate_allocation RENAME COLUMN rateId TO rate_id');
        $this->addSql('ALTER TABLE fusio_rate_allocation RENAME COLUMN routeId TO route_id');
        $this->addSql('ALTER TABLE fusio_rate_allocation RENAME COLUMN appId TO app_id');
        
        $this->addSql('ALTER TABLE fusio_user RENAME COLUMN remoteId TO remote_id');
        
        $this->addSql('ALTER TABLE fusio_scope_routes RENAME COLUMN scopeId TO scope_id');
        $this->addSql('ALTER TABLE fusio_scope_routes RENAME COLUMN routeId TO route_id');
        
        $this->addSql('ALTER TABLE fusio_user_grant RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_user_grant RENAME COLUMN appId TO app_id');
        
        $this->addSql('ALTER TABLE fusio_user_scope RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE fusio_user_scope RENAME COLUMN scopeId TO scope_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
