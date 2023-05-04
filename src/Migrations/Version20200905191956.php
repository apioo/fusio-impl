<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905191956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Insert test data';
    }

    public function up(Schema $schema) : void
    {
        $this->skipIf(!defined('FUSIO_IN_TEST'), 'Skipped test data');

        $appTable = $schema->createTable('app_news');
        $appTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $appTable->addColumn('title', 'string', ['length' => 64]);
        $appTable->addColumn('content', 'string', ['length' => 255]);
        $appTable->addColumn('date', 'datetime');
        $appTable->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $this->skipIf(!defined('FUSIO_IN_TEST'), 'Skipped test data');

        $schema->dropTable('app_news');
    }

    /**
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
