<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241117201015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE fixture (
                id INT AUTO_INCREMENT NOT NULL,
                competition_id INT NOT NULL,
                season_id INT NOT NULL,
                home_team_id INT NOT NULL,
                away_team_id INT NOT NULL,
                status VARCHAR(255) NOT NULL,
                matchday INT NOT NULL,
                home_score INT DEFAULT NULL,
                away_score INT DEFAULT NULL,
                start_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                provider_fixture_id INT DEFAULT NULL,
                INDEX IDX_5E540EE7B39D312 (competition_id),
                INDEX IDX_5E540EE4EC001D1 (season_id),
                INDEX IDX_5E540EE9C4C13F6 (home_team_id),
                INDEX IDX_5E540EE45185D02 (away_team_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EE7B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EE4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EE9C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EE45185D02 FOREIGN KEY (away_team_id) REFERENCES team (id)');
        $this->addSql('DROP INDEX provider_team_id ON team');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EE7B39D312');
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EE4EC001D1');
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EE9C4C13F6');
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EE45185D02');
        $this->addSql('DROP TABLE fixture');
        $this->addSql('CREATE UNIQUE INDEX provider_team_id ON team (provider_team_id)');
    }
}
