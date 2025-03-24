<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241120184023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fixture_prediction (
            id INT AUTO_INCREMENT NOT NULL,
            fixture_id INT NOT NULL,
            user_id INT NOT NULL,
            home_score INT NOT NULL,
            away_score INT NOT NULL,
            INDEX IDX_86CADA71E524616D (fixture_id),
            INDEX IDX_86CADA71A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fixture_prediction ADD CONSTRAINT FK_86CADA71E524616D FOREIGN KEY (fixture_id) REFERENCES fixture (id)');
        $this->addSql('ALTER TABLE fixture_prediction ADD CONSTRAINT FK_86CADA71A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fixture_prediction DROP FOREIGN KEY FK_86CADA71E524616D');
        $this->addSql('ALTER TABLE fixture_prediction DROP FOREIGN KEY FK_86CADA71A76ED395');
        $this->addSql('DROP TABLE fixture_prediction');
    }
}
