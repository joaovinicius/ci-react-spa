<?php

namespace App\Models;

use App\Entities\User;
use CodeIgniter\Model;

class UserModel extends Model
{
  protected $table = 'users';

  protected $primaryKey = 'id';
  protected $returnType = User::class;

  protected $beforeInsert = ['hashPassword', 'formatRole'];
  protected $beforeUpdate = ['hashPassword', 'formatRole'];

  protected $useSoftDeletes = true;
  protected $allowedFields = [
    'email',
    'name',
    'password',
    'phone',
    'bio',
    'email_verified',
    'org_id',
    'tenant_id',
    'nonce',
    'role'
  ];
  protected $useTimestamps = true;
  protected $validationRules = [
    'email' => 'required|valid_email|max_length[255]|is_unique[users.email,id,{id}]',
    'name' => 'required|min_length[3]|max_length[255]',
    'password' => 'required|min_length[8]',
    'phone' => 'permit_empty|max_length[50]',
    'bio' => 'permit_empty',
    'email_verified' => 'permit_empty|in_list[0,1]',
    'org_id' => 'permit_empty|integer',
    'tenant_id' => 'permit_empty|integer',
    'nonce' => 'permit_empty|max_length[255]',
    'role' => 'permit_empty'
  ];
  protected $validationMessages = [
    'email' => [
      'required' => 'The email field is required.',
      'valid_email' => 'The email field must contain a valid email address.',
      'max_length' => 'The email field cannot exceed {param} characters in length.',
      'is_unique' => 'This email is already registered.'
    ],
    'name' => [
      'required' => 'The name field is required.',
      'min_length' => 'The name field must be at least {param} characters in length.',
      'max_length' => 'The name field cannot exceed {param} characters in length.'
    ],
    'password' => [
      'required' => 'The password field is required.',
      'min_length' => 'The password field must be at least {param} characters in length.'
    ],
    'phone' => [
      'max_length' => 'The phone field cannot exceed {param} characters in length.'
    ]
  ];
  protected $skipValidation = false;
  protected $deletedField = 'deleted_at';

  protected function hashPassword(array $data)
  {
    if (empty($data['data']['password'])) {
      unset($data['data']['password']);
      return $data;
    }

    $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

    return $data;
  }

  protected function formatRole(array $data): array
  {
    if (!empty($data['data']['role']) && is_array($data['data']['role'])) {
      $data['data']['role'] = implode(',', $data['data']['role']);
    }
    return $data;
  }
}