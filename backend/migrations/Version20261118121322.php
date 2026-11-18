<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Splits user_token into one table per token type. Links already sent by
 * email stop working.
 */
final class Version20261118121322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replaced user_token with verification, email change and password reset token tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_verification_token (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_6D88E690A76ED395 (user_id), UNIQUE INDEX UNIQ_USER_VERIFICATION_TOKEN_HASH (token_hash), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_email_change_token (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, token_hash VARCHAR(64) NOT NULL, new_email VARCHAR(180) NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_FAB01CDEA76ED395 (user_id), UNIQUE INDEX UNIQ_USER_EMAIL_CHANGE_TOKEN_HASH (token_hash), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_password_reset_token (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_D55A77C9A76ED395 (user_id), UNIQUE INDEX UNIQ_USER_PASSWORD_RESET_TOKEN_HASH (token_hash), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_verification_token ADD CONSTRAINT FK_6D88E690A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_email_change_token ADD CONSTRAINT FK_FAB01CDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_password_reset_token ADD CONSTRAINT FK_D55A77C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63A76ED395');
        $this->addSql('DROP TABLE user_token');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(255) NOT NULL, secret VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, payload JSON NOT NULL, selector VARCHAR(32) NOT NULL, INDEX IDX_BDF55A63A76ED395 (user_id), UNIQUE INDEX UNIQ_USER_TOKEN_SELECTOR (selector), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_verification_token DROP FOREIGN KEY FK_6D88E690A76ED395');
        $this->addSql('ALTER TABLE user_email_change_token DROP FOREIGN KEY FK_FAB01CDEA76ED395');
        $this->addSql('ALTER TABLE user_password_reset_token DROP FOREIGN KEY FK_D55A77C9A76ED395');
        $this->addSql('DROP TABLE user_verification_token');
        $this->addSql('DROP TABLE user_email_change_token');
        $this->addSql('DROP TABLE user_password_reset_token');
    }
}
