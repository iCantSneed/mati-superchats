<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version00001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'https://github.com/iCantSneed/mati-superchats/issues/45';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE stream (id INT NOT NULL, date DATE NOT NULL, prev_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_F0E9BE1CB168B8C0 (prev_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    // Group superchats by date and insert into the Stream table with a strictly increasing unique ID
    $this->addSql('INSERT INTO stream (date, id) SELECT date(convert_tz(created, \'+00:00\', \'-06:00\')) as date, (unix_timestamp(created) / 1000) as id FROM `superchat` GROUP by date');
    // Populate prev_id based on previous rows' id
    $this->addSql('UPDATE stream A set A.prev_id = (select B.id from stream B where B.date < A.date order by B.date DESC limit 1)');
    $this->addSql('ALTER TABLE stream ADD CONSTRAINT FK_F0E9BE1CB168B8C0 FOREIGN KEY (prev_id) REFERENCES stream (id)');
    $this->addSql('ALTER TABLE superchat ADD stream_id INT NOT NULL, CHANGE created created DATETIME NOT NULL');
    // Update superchats' stream ID
    $this->addSql('UPDATE superchat SET stream_id = (SELECT id FROM stream WHERE date = date(convert_tz(superchat.created, \'+00:00\', \'-06:00\')))');
    $this->addSql('ALTER TABLE superchat ADD CONSTRAINT FK_B80DA491D0ED463E FOREIGN KEY (stream_id) REFERENCES stream (id)');
    $this->addSql('CREATE INDEX IDX_B80DA491D0ED463E ON superchat (stream_id)');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE stream DROP FOREIGN KEY FK_F0E9BE1CB168B8C0');
    $this->addSql('ALTER TABLE superchat DROP FOREIGN KEY FK_B80DA491D0ED463E');
    $this->addSql('DROP TABLE stream');
    $this->addSql('DROP INDEX IDX_B80DA491D0ED463E ON superchat');
    $this->addSql('ALTER TABLE superchat DROP stream_id, CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
  }
}
