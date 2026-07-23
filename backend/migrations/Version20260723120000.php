<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260723120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename user.tag to username and drop display_name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` RENAME INDEX UNIQ_USER_TAG TO UNIQ_USER_USERNAME');
        $this->addSql('ALTER TABLE `user` CHANGE tag username VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` DROP display_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD display_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE username tag VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` RENAME INDEX UNIQ_USER_USERNAME TO UNIQ_USER_TAG');
    }
}
