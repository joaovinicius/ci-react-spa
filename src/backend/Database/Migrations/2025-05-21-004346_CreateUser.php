<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTable extends Migration
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
      'email' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'name' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'password' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'phone' => [
        'type' => 'VARCHAR',
        'constraint' => 50,
        'null' => true,
      ],
      'bio' => [
        'type' => 'TEXT',
        'null' => true,
      ],
      'email_verified' => [
        'type' => 'BOOLEAN',
        'null' => true,
        'default' => false,
      ],
      'org_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'null' => true,
      ],
      'tenant_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'null' => true,
      ],
      'nonce' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => true,
      ],
      'created_at' => [
        'type' => 'DATETIME',
        'null' => false,
        'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
      ],
      'updated_at' => [
        'type' => 'DATETIME',
        'null' => false,
        'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
      ],
      'deleted_at' => [
        'type' => 'DATETIME',
        'null' => true,
        'default' => null,
      ],
    ]);

    $this->forge->addKey('id', true);
    // Adding composite unique key for email, org_id, and tenant_id
    $this->forge->addUniqueKey(['email', 'org_id', 'tenant_id'], 'unique_email_org_tenant');

    // Adding foreign keys
    $this->forge->addForeignKey('org_id', 'orgs', 'id', 'SET NULL', 'SET NULL');
    $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'SET NULL', 'SET NULL');

    $this->forge->createTable('users');
  }

  public function down()
  {
    $this->forge->dropTable('users');
  }
}