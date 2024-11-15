<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241115204832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE country (
                id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL,
                iso_3166_1_alpha_2 VARCHAR(2) NOT NULL,
                iso_3166_1_alpha_3 VARCHAR(3) NOT NULL,
                iso_3166_1_numeric SMALLINT NOT NULL,
                iso_3166_2 VARCHAR(6),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE country');
    }
}
