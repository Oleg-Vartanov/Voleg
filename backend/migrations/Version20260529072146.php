<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529072146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE currency (
                id INT AUTO_INCREMENT NOT NULL,
                code VARCHAR(3) NOT NULL,
                decimal_places INT NOT NULL,
                symbol VARCHAR(8) NOT NULL,
                UNIQUE INDEX UNIQ_CURRENCY_CODE (code),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('
            CREATE TABLE se_category (
                id INT AUTO_INCREMENT NOT NULL,
                tag VARCHAR(64) NOT NULL,
                title VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_SE_CATEGORY_TAG (tag),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql(
            "INSERT INTO se_category (id, tag, title) VALUES (1, 'other', 'Other')"
        );
        $this->addSql('
            CREATE TABLE se_connection (
                id INT AUTO_INCREMENT NOT NULL,
                created_at DATETIME NOT NULL,
                status VARCHAR(16) NOT NULL,
                requested_by_id INT NOT NULL,
                user_a_id INT NOT NULL,
                user_b_id INT NOT NULL,
                INDEX IDX_99807F224DA1E751 (requested_by_id),
                INDEX IDX_99807F22415F1F91 (user_a_id),
                INDEX IDX_99807F2253EAB07F (user_b_id),
                UNIQUE INDEX UNIQ_SE_CONNECTION_PAIR (user_a_id, user_b_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('
            CREATE TABLE se_expense (
                id INT AUTO_INCREMENT NOT NULL,
                paid_by_user_id INT NOT NULL,
                category_id INT NOT NULL,
                currency_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                amount INT NOT NULL,
                description LONGTEXT DEFAULT NULL,
                expense_date DATE NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_D45021B4B63F5575 (paid_by_user_id),
                INDEX IDX_D45021B412469DE2 (category_id),
                INDEX IDX_D45021B438248176 (currency_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('
            CREATE TABLE se_expense_split (
                id INT AUTO_INCREMENT NOT NULL,
                expense_id INT NOT NULL,
                user_id INT NOT NULL,
                amount INT NOT NULL,
                INDEX IDX_7BAB314F395DB7B (expense_id),
                INDEX IDX_7BAB314A76ED395 (user_id),
                UNIQUE INDEX UNIQ_SE_EXPENSE_SPLIT (expense_id, user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');

        $this->addSql('ALTER TABLE se_connection ADD CONSTRAINT FK_99807F224DA1E751 FOREIGN KEY (requested_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE se_connection ADD CONSTRAINT FK_99807F22415F1F91 FOREIGN KEY (user_a_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE se_connection ADD CONSTRAINT FK_99807F2253EAB07F FOREIGN KEY (user_b_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE se_expense ADD CONSTRAINT FK_D45021B4B63F5575 FOREIGN KEY (paid_by_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE se_expense ADD CONSTRAINT FK_D45021B412469DE2 FOREIGN KEY (category_id) REFERENCES se_category (id)');
        $this->addSql('ALTER TABLE se_expense ADD CONSTRAINT FK_D45021B438248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE se_expense_split ADD CONSTRAINT FK_7BAB314F395DB7B FOREIGN KEY (expense_id) REFERENCES se_expense (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE se_expense_split ADD CONSTRAINT FK_7BAB314A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE se_connection DROP FOREIGN KEY FK_99807F224DA1E751');
        $this->addSql('ALTER TABLE se_connection DROP FOREIGN KEY FK_99807F22415F1F91');
        $this->addSql('ALTER TABLE se_connection DROP FOREIGN KEY FK_99807F2253EAB07F');
        $this->addSql('ALTER TABLE se_expense DROP FOREIGN KEY FK_D45021B4B63F5575');
        $this->addSql('ALTER TABLE se_expense DROP FOREIGN KEY FK_D45021B412469DE2');
        $this->addSql('ALTER TABLE se_expense DROP FOREIGN KEY FK_D45021B438248176');
        $this->addSql('ALTER TABLE se_expense_split DROP FOREIGN KEY FK_7BAB314F395DB7B');
        $this->addSql('ALTER TABLE se_expense_split DROP FOREIGN KEY FK_7BAB314A76ED395');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE se_category');
        $this->addSql('DROP TABLE se_connection');
        $this->addSql('DROP TABLE se_expense');
        $this->addSql('DROP TABLE se_expense_split');
    }
}
