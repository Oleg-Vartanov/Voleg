<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pending email change fields for user verification';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user
                ADD email_change VARCHAR(180) DEFAULT NULL,
                ADD email_change_code VARCHAR(255) DEFAULT NULL,
                ADD email_change_code_expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE `user`
                DROP email_change,
                DROP email_change_code,
                DROP email_change_code_expire_at
        ');
    }
}
