<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];
  protected $casts = ['role' => 'csv'];

  public function toPublicArray(): array
  {
    return [
      'id' => (int)$this->id,
      'email' => $this->email,
      'name' => $this->name,
      'phone' => $this->phone,
      'bio' => $this->bio,
      'email_verified' => (bool)$this->email_verified,
      'org_id' => (int)$this->org_id,
      'tenant_id' => (int)$this->tenant_id,
      'role' => $this->role,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }

  public function verifyPassword(string $password): bool
  {
    return password_verify($password, $this->attributes['password']);
  }
}