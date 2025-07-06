<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706155817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_identifier_email TO UNIQ_USER_EMAIL');
        $this->addSql('ALTER TABLE user RENAME INDEX tag TO UNIQ_USER_TAG');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_user_tag TO tag');
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_user_email TO UNIQ_IDENTIFIER_EMAIL');
    }
}
