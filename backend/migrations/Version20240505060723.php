<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240505060723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD verification_code VARCHAR(255) DEFAULT NULL, ADD verification_code_expire_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD created_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE verified verified TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP verification_code, DROP verification_code_expire_at, DROP created_at, CHANGE verified verified TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
