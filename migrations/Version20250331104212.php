<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331104212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE budget (id SERIAL NOT NULL, owner_user_id INT NOT NULL, category VARCHAR(255) NOT NULL, max_spend DOUBLE PRECISION NOT NULL, color VARCHAR(20) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_73F2F77B2B18554A ON budget (owner_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE party (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pots (id SERIAL NOT NULL, owner_user_id INT NOT NULL, name VARCHAR(255) NOT NULL, balance DOUBLE PRECISION NOT NULL, target DOUBLE PRECISION NOT NULL, color VARCHAR(15) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8BAF6FE92B18554A ON pots (owner_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription (id SERIAL NOT NULL, owner_user_id INT NOT NULL, name VARCHAR(255) NOT NULL, day_of_month SMALLINT NOT NULL, frequency VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A3C664D32B18554A ON subscription (owner_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transaction (id SERIAL NOT NULL, user_owner_id INT NOT NULL, parties_id INT NOT NULL, transected_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, category VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D19EB185F9 ON transaction (user_owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1362AAF23 ON transaction (parties_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN transaction.transected_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, balance DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B2B18554A FOREIGN KEY (owner_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pots ADD CONSTRAINT FK_8BAF6FE92B18554A FOREIGN KEY (owner_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D32B18554A FOREIGN KEY (owner_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D19EB185F9 FOREIGN KEY (user_owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1362AAF23 FOREIGN KEY (parties_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B2B18554A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pots DROP CONSTRAINT FK_8BAF6FE92B18554A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D32B18554A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D19EB185F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP CONSTRAINT FK_723705D1362AAF23
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE budget
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE party
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pots
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
