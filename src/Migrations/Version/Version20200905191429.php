<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905191429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Insert new installation data';
    }

    public function up(Schema $schema) : void
    {
        // moved to Version20200905081453::postUp
    }

    public function down(Schema $schema) : void
    {
    }

    /**
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
