<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117000400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Require owners after the teaching data transition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE event_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, description CLOB NOT NULL, starts_at DATETIME NOT NULL, category_id INTEGER DEFAULT NULL, owner_id INTEGER NOT NULL, FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL, FOREIGN KEY (owner_id) REFERENCES app_user (id))',
        );
        $this->addSql(
            'INSERT INTO event_new (id, title, description, starts_at, category_id, owner_id) SELECT id, title, description, starts_at, category_id, owner_id FROM event',
        );
        $this->addSql('DROP TABLE event');
        $this->addSql('ALTER TABLE event_new RENAME TO event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA712469DE2 ON event (category_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77E3C61F9 ON event (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restoring nullable owners requires a separate data migration.');
    }
}
