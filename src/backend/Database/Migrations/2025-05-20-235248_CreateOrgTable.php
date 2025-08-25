<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateOrgTable extends Migration
{
  public function up()
  {
    $this->forge->addField([
      'id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'auto_increment' => true,
      ],
      'domain' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
        'unique' => true,
      ],
      'slug' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
        'unique' => true,
      ],
      'name' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'created_at' => [
        'type' => 'DATETIME',
        'null' => false,
        'default' => new RawSql('CURRENT_TIMESTAMP'),
      ],
      'updated_at' => [
        'type' => 'DATETIME',
        'null' => false,
        'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
      ],
      'deleted_at' => [
        'type' => 'DATETIME',
        'null' => true,
        'default' => null,
      ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->createTable('orgs');
  }

  public function down()
  {
    $this->forge->dropTable('orgs');
  }
}