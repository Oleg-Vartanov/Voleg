<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add se_adjustment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE se_adjustment (
                id INT AUTO_INCREMENT NOT NULL,
                created_by_user_id INT NOT NULL,
                other_user_id INT NOT NULL,
                currency_id INT NOT NULL,
                amount INT NOT NULL,
                description LONGTEXT DEFAULT NULL,
                adjustment_date DATE NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_SE_ADJUSTMENT_CREATED_BY (created_by_user_id),
                INDEX IDX_SE_ADJUSTMENT_OTHER_USER (other_user_id),
                INDEX IDX_SE_ADJUSTMENT_CURRENCY (currency_id),
                INDEX IDX_SE_ADJUSTMENT_BALANCE_CREATED_BY (created_by_user_id, currency_id),
                INDEX IDX_SE_ADJUSTMENT_BALANCE_OTHER (other_user_id, currency_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE se_adjustment
            ADD CONSTRAINT FK_SE_ADJUSTMENT_CREATED_BY
            FOREIGN KEY (created_by_user_id) REFERENCES user (id) ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE se_adjustment
            ADD CONSTRAINT FK_SE_ADJUSTMENT_OTHER_USER
            FOREIGN KEY (other_user_id) REFERENCES user (id) ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE se_adjustment
            ADD CONSTRAINT FK_SE_ADJUSTMENT_CURRENCY
            FOREIGN KEY (currency_id) REFERENCES currency (id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE se_adjustment DROP FOREIGN KEY FK_SE_ADJUSTMENT_CREATED_BY');
        $this->addSql('ALTER TABLE se_adjustment DROP FOREIGN KEY FK_SE_ADJUSTMENT_OTHER_USER');
        $this->addSql('ALTER TABLE se_adjustment DROP FOREIGN KEY FK_SE_ADJUSTMENT_CURRENCY');
        $this->addSql('DROP TABLE se_adjustment');
    }
}
