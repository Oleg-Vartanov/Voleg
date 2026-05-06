<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506134432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added user_password_reset table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_password_reset (
                id INT AUTO_INCREMENT NOT NULL,
                selector VARCHAR(16) NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_DA84AD0BA76ED395 (user_id),
                UNIQUE INDEX UNIQ_USER_PASSWORD_RESET_SELECTOR (selector),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('
            ALTER TABLE user_password_reset
                ADD CONSTRAINT FK_DA84AD0BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_password_reset DROP FOREIGN KEY FK_DA84AD0BA76ED395');
        $this->addSql('DROP TABLE user_password_reset');
    }
}
