<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerToOrg extends Migration
{
  public function up()
  {
    $this->forge->addColumn('orgs', [
      'user_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'null' => false,
      ],
    ]);
    $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
  }

  public function down()
  {
    $this->forge->dropForeignKey('orgs', 'orgs_user_id_foreign');
    $this->forge->dropColumn('orgs', 'user_id');
  }
}