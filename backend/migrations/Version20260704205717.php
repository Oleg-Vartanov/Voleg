<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704205717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added created_by_user_id to se_expense';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE se_expense ADD created_by_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE se_expense ADD CONSTRAINT FK_D45021B47D182D95 FOREIGN KEY (created_by_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D45021B47D182D95 ON se_expense (created_by_user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE se_expense DROP FOREIGN KEY FK_D45021B47D182D95');
        $this->addSql('DROP INDEX IDX_D45021B47D182D95 ON se_expense');
        $this->addSql('ALTER TABLE se_expense DROP created_by_user_id');
    }
}
