<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220807091731 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $logTable = $schema->getTable('fusio_log');
        if (!$logTable->hasIndex('IDX_LOG_CID')) {
            $logTable->addIndex(['category_id', 'ip', 'date'], 'IDX_LOG_CID');
        }

        $rateTable = $schema->getTable('fusio_rate');
        if (!$rateTable->hasIndex('IDX_RATE_S')) {
            $rateTable->addIndex(['status'], 'IDX_RATE_S');
        }

        $routesTable = $schema->getTable('fusio_routes');
        if (!$routesTable->hasIndex('IDX_ROUTE_S')) {
            $routesTable->addIndex(['status'], 'IDX_ROUTE_S');
        }

        if (!$routesTable->hasIndex('IDX_ROUTE_CS')) {
            $routesTable->addIndex(['category_id', 'status'], 'IDX_ROUTE_CS');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
