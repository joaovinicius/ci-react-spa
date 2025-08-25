<?php

namespace App\Models;

use App\Entities\Org;
use CodeIgniter\Model;

class OrgModel extends Model
{
  protected $table = 'orgs';

  protected $primaryKey = 'id';
  protected $returnType = Org::class;
  protected $useSoftDeletes = true;
  protected $allowedFields = [
    'domain',
    'slug',
    'name',
    'user_id'
  ];
  protected $useTimestamps = true;
  protected $validationRules = [
    'domain' => 'required|valid_url|is_unique[tenants.domain,id,{id}]|min_length[3]|max_length[255]',
    'slug' => 'required|alpha_dash|is_unique[orgs.slug,id,{id}]|min_length[3]|max_length[255]',
    'name' => 'required|min_length[3]|max_length[255]',
    'user_id' => 'required|integer',
  ];
  protected $validationMessages = [
    'domain' => [
      'required' => 'The domain field is required.',
      'valid_url' => 'The domain must be a valid URL.',
      'is_unique' => 'This domain is already taken.',
      'min_length' => 'The domain field must be at least {param} characters in length.',
      'max_length' => 'The domain field cannot exceed {param} characters in length.'
    ],
    'slug' => [
      'required' => 'The slug field is required.',
      'alpha_dash' => 'The slug field may only contain alphanumeric characters, underscores, and dashes.',
      'is_unique' => 'This slug is already taken.',
      'min_length' => 'The slug field must be at least {param} characters in length.',
      'max_length' => 'The slug field cannot exceed {param} characters in length.',
    ],
    'name' => [
      'required' => 'The name field is required.',
      'min_length' => 'The name field must be at least {param} characters in length.',
      'max_length' => 'The name field cannot exceed {param} characters in length.',
    ],
    'user_id' => [
      'required' => 'The owner ID is required.',
      'integer' => 'The owner ID must be an integer.',
    ]
  ];
  protected $skipValidation = false;
  protected $deletedField = 'deleted_at';
}