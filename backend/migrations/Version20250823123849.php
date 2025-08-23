<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250823123849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added prefix to table names fp_';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE competition TO fp_competition');
        $this->addSql('RENAME TABLE fixture TO fp_fixture');
        $this->addSql('RENAME TABLE fixture_prediction TO fp_fixture_prediction');
        $this->addSql('RENAME TABLE season TO fp_season');
        $this->addSql('RENAME TABLE team TO fp_team');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE fp_competition TO competition');
        $this->addSql('RENAME TABLE fp_fixture TO fixture');
        $this->addSql('RENAME TABLE fp_fixture_prediction TO fixture_prediction');
        $this->addSql('RENAME TABLE fp_season TO season');
        $this->addSql('RENAME TABLE fp_team TO team');
    }
}
