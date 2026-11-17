<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add categories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(80) NOT NULL)',
        );
        $this->addSql(
            'ALTER TABLE event ADD COLUMN category_id INTEGER DEFAULT NULL REFERENCES category (id) ON DELETE SET NULL',
        );
        $this->addSql('CREATE INDEX IDX_3BAE0AA712469DE2 ON event (category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE category');
    }
}
