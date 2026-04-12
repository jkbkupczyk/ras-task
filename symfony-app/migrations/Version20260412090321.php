<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412090321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create phoenix token tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS phoenix_token
            (
                id          BIGSERIAL    NOT NULL,
                user_id     BIGINT       NOT NULL,
                token       VARCHAR(512) NOT NULL,
                modify_date TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT pk_phoenix_token PRIMARY KEY (id),
                CONSTRAINT fk_phoenix_token_user_id FOREIGN KEY (user_id)
                    REFERENCES users (id) ON DELETE CASCADE
            );
        ');

        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uidx_phoenix_token_user_id ON phoenix_token (user_id);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS phoenix_token;');
    }
}
