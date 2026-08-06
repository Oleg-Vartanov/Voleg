<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805212400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace currency symbols with currency names';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE currency ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE currency SET name = code');
        $this->addSql('ALTER TABLE currency MODIFY name VARCHAR(255) NOT NULL, DROP symbol');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE currency ADD symbol VARCHAR(8) NOT NULL DEFAULT ''");
        $this->addSql('ALTER TABLE currency DROP name');
    }
}
