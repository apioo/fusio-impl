<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210703110558 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $pageTable = $schema->createTable('fusio_page');
        $pageTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $pageTable->addColumn('status', 'integer', ['default' => Table\Page::STATUS_ACTIVE]);
        $pageTable->addColumn('title', 'string', ['length' => 255]);
        $pageTable->addColumn('slug', 'string', ['length' => 255]);
        $pageTable->addColumn('content', 'text');
        $pageTable->addColumn('date', 'datetime');
        $pageTable->setPrimaryKey(['id']);
        $pageTable->addUniqueIndex(['slug']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('fusio_page');
    }

    /**
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
