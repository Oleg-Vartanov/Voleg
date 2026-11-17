<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_refresh_token';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_refresh_token (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                UNIQUE INDEX UNIQ_USER_REFRESH_TOKEN_HASH (token_hash),
                INDEX IDX_USER_REFRESH_TOKEN_USER (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE user_refresh_token
            ADD CONSTRAINT FK_USER_REFRESH_TOKEN_USER
            FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_refresh_token DROP FOREIGN KEY FK_USER_REFRESH_TOKEN_USER');
        $this->addSql('DROP TABLE user_refresh_token');
    }
}
