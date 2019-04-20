<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190416200104 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $userAttributeTable = $schema->createTable('fusio_user_attribute');
        $userAttributeTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $userAttributeTable->addColumn('user_id', 'integer');
        $userAttributeTable->addColumn('name', 'string');
        $userAttributeTable->addColumn('value', 'string');
        $userAttributeTable->setPrimaryKey(['id']);

        $userAttributeTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_attribute_user_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('fusio_user_attribute');
    }
}
