<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Tenant extends Entity
{
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];

  public function toPublicArray(): array
  {
    return [
      'id' => (int)$this->id,
      'name' => $this->name,
      'slug' => $this->slug,
      'domain' => $this->domain,
      'config' => json_decode($this->config),
      'org_id' => (int)$this->org_id,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}