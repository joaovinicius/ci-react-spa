<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateTenantTable extends Migration
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
      'config' => [
        'type' => 'JSON',
        'null' => true,
      ],
      'status' => [
        'type' => 'ENUM',
        'constraint' => ['draft', 'published', 'archived'],
        'default' => 'draft',
      ],
      'name' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'org_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
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
    $this->forge->addForeignKey('org_id', 'orgs', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('tenants');
  }

  public function down()
  {
    $this->forge->dropTable('tenants');
  }
}