<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250302111607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD tag VARCHAR(255) NULL');
        $this->addSql('UPDATE user SET tag = id');
        $this->addSql('ALTER TABLE user MODIFY tag VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX tag ON user (tag)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP tag');
        $this->addSql('DROP INDEX tag ON user');
    }
}
