<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\NewInstallation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220927201645 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addMetaDataColumn($schema, 'fusio_action');
        $this->addMetaDataColumn($schema, 'fusio_app');
        $this->addMetaDataColumn($schema, 'fusio_connection');
        $this->addMetaDataColumn($schema, 'fusio_cronjob');
        $this->addMetaDataColumn($schema, 'fusio_event');
        $this->addMetaDataColumn($schema, 'fusio_page');
        $this->addMetaDataColumn($schema, 'fusio_plan');
        $this->addMetaDataColumn($schema, 'fusio_rate');
        $this->addMetaDataColumn($schema, 'fusio_routes');
        $this->addMetaDataColumn($schema, 'fusio_schema');
        $this->addMetaDataColumn($schema, 'fusio_scope');
        $this->addMetaDataColumn($schema, 'fusio_user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    private function addMetaDataColumn(Schema $schema, string $tableName)
    {
        $table = $schema->getTable($tableName);
        if (!$table->hasColumn('metadata')) {
            $table->addColumn('metadata', 'text', ['notnull' => false]);
        }
    }
}
