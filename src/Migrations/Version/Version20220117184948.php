<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220117184948 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $configs = [
            [Table\Config::FORM_STRING, 'provider_facebook_key', 'Facebook app key', ''],
            [Table\Config::FORM_STRING, 'provider_google_key', 'Google app key', ''],
            [Table\Config::FORM_STRING, 'provider_github_key', 'GitHub app key', ''],
            [Table\Config::FORM_STRING, 'recaptcha_key', 'ReCaptcha key', ''],
        ];

        foreach ($configs as $row) {
            $this->addSql('INSERT INTO fusio_config (type, name, description, value) VALUES (?, ?, ?, ?)', $row);
        }
    }

    public function down(Schema $schema) : void
    {
    }
}
