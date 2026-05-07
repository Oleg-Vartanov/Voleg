<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260507131849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added user_token table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_token (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                type VARCHAR(255) NOT NULL,
                selector VARCHAR(32) NOT NULL,
                secret VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                payload JSON NOT NULL,
                INDEX IDX_BDF55A63A76ED395 (user_id),
                UNIQUE INDEX UNIQ_USER_TOKEN_SELECTOR (selector),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63A76ED395');
        $this->addSql('DROP TABLE user_token');
    }
}
