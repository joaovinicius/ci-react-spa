<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
  public function up()
  {
    // Add a SET column so a user can have one or more roles simultaneously.
    $this->forge->addColumn('users', [
      // Raw definition for MySQL SET with all four roles
      "role SET('Admin','User','OrgAdmin','TenantAdmin') 
             NOT NULL 
             DEFAULT 'User'"
    ]);
  }

  public function down()
  {
    // Roll back by dropping the role column
    $this->forge->dropColumn('users', 'role');
  }
}