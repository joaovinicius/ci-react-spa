<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Org extends Entity
{
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];
  protected $casts = [
    'user_id' => 'integer',
  ];

  public function toPublicArray(): array
  {
    return [
      'id' => $this->id,
      'domain' => $this->domain,
      'slug' => $this->slug,
      'name' => $this->name,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}