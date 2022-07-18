<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718210602 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
    }

    public function down(Schema $schema) : void
    {
    }

    public function postUp(Schema $schema): void
    {
        $routes = [
            '/backend/routes/provider' => '/backend/generator',
            '/backend/routes/provider/:provider' => '/backend/generator/:provider',
        ];

        foreach ($routes as $oldPath => $newPath) {
            $this->connection->executeStatement('UPDATE fusio_routes SET path = :new_path WHERE path = :old_path', [
                'new_path' => $newPath,
                'old_path' => $oldPath,
            ]);
        }
    }
}
