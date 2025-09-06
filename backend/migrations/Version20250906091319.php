<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250906091319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add current_season to fp_competition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fp_competition ADD current_season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fp_competition ADD CONSTRAINT FK_8F3299FC95E6B07D FOREIGN KEY (current_season_id) REFERENCES fp_season (id)');
        $this->addSql('CREATE INDEX IDX_8F3299FC95E6B07D ON fp_competition (current_season_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fp_competition DROP FOREIGN KEY FK_8F3299FC95E6B07D');
        $this->addSql('DROP INDEX IDX_8F3299FC95E6B07D ON fp_competition');
        $this->addSql('ALTER TABLE fp_competition DROP current_season_id');
    }
}
