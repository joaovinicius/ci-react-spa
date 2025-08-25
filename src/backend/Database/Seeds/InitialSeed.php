<?php

namespace App\Database\Seeds;


use CodeIgniter\Database\Seeder;

class InitialSeed extends Seeder
{
  public function run()
  {
    $this->db->table('orgs')->insert([
      'id' => 1,
      'slug' => 'org',
      "domain" => "https://new-org.com",
      'name' => 'Demo Organization',
    ]);

    $this->db->table('tenants')->insert([
      'id' => 1,
      "domain" => "https://new-tenant.com",
      'slug' => 'tenant',
      "name" => "New Tenant",
      "status" => "draft",
      'config' => '{}',
      "org_id" => 1
    ]);

    $this->db->table('users')->insert([
      'id' => 1,
      "email" => "user@example.com",
      "name" => "User Admin",
      "password" => password_hash("securepassword", PASSWORD_DEFAULT),
      "phone" => "+1234567890",
      "bio" => "User biography",
      "email_verified" => false,
      "org_id" => 1,
      "tenant_id" => 1,
      "role" => ["Admin"]
    ]);
  }
}
