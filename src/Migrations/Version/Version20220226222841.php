<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220226222841 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $configs = [
            [Table\Config::FORM_STRING, 'mail_points_subject', 'Subject of the points threshold mail', 'Fusio points threshold reached'],
            [Table\Config::FORM_TEXT, 'mail_points_body', 'Body of the points threshold mail', 'Hello {name},' . "\n\n" . 'your account has reached the configured threshold of {points} points.' . "\n" . 'If your account reaches 0 points your are not longer able to invoke specific endpoints.' . "\n" . 'To prevent this please go to the developer portal to purchase new points:' . "\n" . '{apps_url}/developer'],
            [Table\Config::FORM_NUMBER, 'points_threshold', 'If a user goes below this points threshold we send an information to the user', 0],
        ];

        foreach ($configs as $row) {
            $id = $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = ?', [$row[1]]);
            if (empty($id)) {
                $this->addSql('INSERT INTO fusio_config (type, name, description, value) VALUES (?, ?, ?, ?)', $row);
            }
        }
    }

    public function down(Schema $schema) : void
    {
    }
}
