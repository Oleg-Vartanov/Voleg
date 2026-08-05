<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260804234000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reference-data natural keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_COUNTRY_NAME ON country (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FP_COMPETITION_CODE ON fp_competition (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FP_SEASON_YEAR ON fp_season (year)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_COUNTRY_NAME ON country');
        $this->addSql('DROP INDEX UNIQ_FP_COMPETITION_CODE ON fp_competition');
        $this->addSql('DROP INDEX UNIQ_FP_SEASON_YEAR ON fp_season');
    }
}
