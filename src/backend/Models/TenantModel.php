<?php

namespace App\Models;

use App\Entities\Tenant;
use CodeIgniter\Model;

class TenantModel extends Model
{
  protected $table = 'tenants';
  protected $primaryKey = 'id';
  protected $returnType = Tenant::class;
  protected $useSoftDeletes = true;
  protected $allowedFields = [
    'domain',
    'slug',
    'config',
    'status',
    'name',
    'org_id'
  ];
  protected $useTimestamps = true;
  protected $validationRules = [
    'domain' => 'required|valid_url|is_unique[tenants.domain,id,{id}]|min_length[3]|max_length[255]',
    'slug' => 'required|alpha_dash|is_unique[orgs.slug,id,{id}]|min_length[3]|max_length[255]',
    'name' => 'required|min_length[3]|max_length[255]',
    'status' => 'required|in_list[draft,published,archived]',
    'config' => 'required|valid_json',
    'org_id' => 'required|numeric|is_not_unique[orgs.id]'
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
      'max_length' => 'The name field cannot exceed {param} characters in length.'
    ],
    'status' => [
      'required' => 'The status field is required.',
      'in_list' => 'The status must be one of: draft, published, archived.'
    ],
    'config' => [
      'required' => 'The config field is required.',
      'valid_json' => 'The config must be a valid JSON string.'
    ],
    'org_id' => [
      'required' => 'The organization ID is required.',
      'numeric' => 'The organization ID must be a number.',
      'is_not_unique' => 'The specified organization does not exist.'
    ]
  ];
  protected $skipValidation = false;
  protected $deletedField = 'deleted_at';
}
