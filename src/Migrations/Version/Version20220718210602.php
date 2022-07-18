<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\DataSyncronizer;
use Fusio\Impl\Table;

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
            '/backend/routes/provider',
            '/backend/routes/provider/:provider',
        ];

        foreach ($routes as $path) {
            $routeId = $this->connection->fetchOne('SELECT id FROM fusio_routes WHERE path = :path', ['path' => $path]);
            if (empty($routeId)) {
                continue;
            }

            $this->connection->update('fusio_routes', ['status' => Table\Route::STATUS_DELETED], ['id' => $routeId]);
        }

        DataSyncronizer::sync($this->connection);
    }
}
