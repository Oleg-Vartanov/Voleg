<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116091008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE competition (
                id INT AUTO_INCREMENT NOT NULL,
                country_id INT DEFAULT NULL,
                name VARCHAR(100) NOT NULL,
                code VARCHAR(10) NOT NULL,
                INDEX IDX_B50A2CB1F92F3E70 (country_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB1F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition DROP FOREIGN KEY FK_B50A2CB1F92F3E70');
        $this->addSql('DROP TABLE competition');
    }
}
