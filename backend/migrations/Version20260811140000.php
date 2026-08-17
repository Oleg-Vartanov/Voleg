<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add covering indexes for split expense balance aggregation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE INDEX IDX_SE_EXPENSE_SPLIT_BALANCE
            ON se_expense_split (user_id, expense_id, amount)
        ');
        $this->addSql('
            CREATE INDEX IDX_SE_EXPENSE_BALANCE
            ON se_expense (paid_by_user_id, currency_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_SE_EXPENSE_SPLIT_BALANCE ON se_expense_split');
        $this->addSql('DROP INDEX IDX_SE_EXPENSE_BALANCE ON se_expense');
    }
}
