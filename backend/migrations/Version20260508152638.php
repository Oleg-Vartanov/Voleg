<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508152638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed user_token fields from user_table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user
                DROP verification_code,
                DROP verification_code_expire_at,
                DROP email_change,
                DROP email_change_code,
                DROP email_change_code_expire_at
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user
                ADD verification_code VARCHAR(255) DEFAULT NULL,
                ADD verification_code_expire_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL,
                ADD email_change VARCHAR(180) DEFAULT NULL,
                ADD email_change_code VARCHAR(255) DEFAULT NULL,
                ADD email_change_code_expire_at DATETIME DEFAULT NULL
        ');
    }
}
