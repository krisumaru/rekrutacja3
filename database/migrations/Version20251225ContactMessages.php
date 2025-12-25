<?php

declare(strict_types=1);

namespace database\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251225ContactMessages extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contact_messages table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_messages (
            id uuid NOT NULL, 
            full_name VARCHAR(255) NOT NULL, 
            email VARCHAR(255) NOT NULL, 
            message TEXT NOT NULL, 
            consent BOOLEAN NOT NULL, 
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
            PRIMARY KEY(id))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_messages');
    }
}
