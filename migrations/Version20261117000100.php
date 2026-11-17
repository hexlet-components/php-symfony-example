<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, description CLOB NOT NULL, starts_at DATETIME NOT NULL)',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event');
    }
}
