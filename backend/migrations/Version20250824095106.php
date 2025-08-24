<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824095106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fp_competition RENAME INDEX idx_b50a2cb1f92f3e70 TO IDX_8F3299FCF92F3E70');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_5e540ee7b39d312 TO IDX_DB5FA16E7B39D312');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_5e540ee4ec001d1 TO IDX_DB5FA16E4EC001D1');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_5e540ee9c4c13f6 TO IDX_DB5FA16E9C4C13F6');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_5e540ee45185d02 TO IDX_DB5FA16E45185D02');
        $this->addSql('ALTER TABLE fp_fixture_prediction RENAME INDEX idx_86cada71e524616d TO IDX_E7D6A78CE524616D');
        $this->addSql('ALTER TABLE fp_fixture_prediction RENAME INDEX idx_86cada71a76ed395 TO IDX_E7D6A78CA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fp_fixture_prediction RENAME INDEX idx_e7d6a78ca76ed395 TO IDX_86CADA71A76ED395');
        $this->addSql('ALTER TABLE fp_fixture_prediction RENAME INDEX idx_e7d6a78ce524616d TO IDX_86CADA71E524616D');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_db5fa16e7b39d312 TO IDX_5E540EE7B39D312');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_db5fa16e45185d02 TO IDX_5E540EE45185D02');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_db5fa16e9c4c13f6 TO IDX_5E540EE9C4C13F6');
        $this->addSql('ALTER TABLE fp_fixture RENAME INDEX idx_db5fa16e4ec001d1 TO IDX_5E540EE4EC001D1');
        $this->addSql('ALTER TABLE fp_competition RENAME INDEX idx_8f3299fcf92f3e70 TO IDX_B50A2CB1F92F3E70');
    }
}
