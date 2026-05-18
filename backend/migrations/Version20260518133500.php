<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518133500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added user_contact table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_contact (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                contact_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_USER_CONTACT_USER (user_id),
                INDEX IDX_USER_CONTACT_CONTACT (contact_id),
                UNIQUE INDEX UNIQ_USER_CONTACT_PAIR (user_id, contact_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        ');
        $this->addSql('ALTER TABLE user_contact ADD CONSTRAINT FK_USER_CONTACT_USER FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_contact ADD CONSTRAINT FK_USER_CONTACT_CONTACT FOREIGN KEY (contact_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_contact DROP FOREIGN KEY FK_USER_CONTACT_USER');
        $this->addSql('ALTER TABLE user_contact DROP FOREIGN KEY FK_USER_CONTACT_CONTACT');
        $this->addSql('DROP TABLE user_contact');
    }
}
