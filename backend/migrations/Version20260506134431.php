<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506134431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DATETIME schema update';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fp_fixture CHANGE start_at start_at DATETIME DEFAULT NULL');
        $this->addSql('
            ALTER TABLE user
                CHANGE verification_code_expire_at verification_code_expire_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL,
                CHANGE created_at created_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL,
                CHANGE email_change_code_expire_at email_change_code_expire_at DATETIME DEFAULT NULL'
        );
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('
            ALTER TABLE messenger_messages
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE available_at available_at DATETIME NOT NULL,
                CHANGE delivered_at delivered_at DATETIME DEFAULT NULL'
        );
        $this->addSql('
            CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fp_fixture CHANGE start_at start_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('
            ALTER TABLE messenger_messages
                CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\''
        );
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('
            ALTER TABLE user
                CHANGE verification_code_expire_at verification_code_expire_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                CHANGE email_change_code_expire_at email_change_code_expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                CHANGE created_at created_at DATETIME DEFAULT \'1970-01-01 00:00:00\' NOT NULL COMMENT \'(DC2Type:datetime_immutable)\''
        );
    }
}
