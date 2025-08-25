<?php

namespace App\Repositories;

use App\Models\TenantModel;

class TenantRepository
{
  protected $model;

  public function __construct()
  {
    $this->model = new TenantModel();
  }

  /**
   * Get paginated tenants
   *
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function findPaginated(int $limit = 0, int $offset = 0): array
  {
    $data = $this->model->findAll($limit, $offset);

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
  }

  /**
   * Get a tenant by ID
   *
   * @param int $id
   * @return array|null|object
   */
  public function find(int $id): array|null|object
  {
    $data = $this->model->where('id', $id)->first();

    return $data?->toPublicArray();
  }

  /**
   * Get a tenant by domain
   *
   * @param string $domain
   * @return array|null|object
   */
  public function findByDomain(string $domain): array|null|object
  {
    $data = $this->model->where('domain', $domain)->first();

    return $data?->toPublicArray();
  }

  /**
   * Get tenants by organization ID
   *
   * @param int $orgId
   * @return array
   */
  public function findByOrgId(int $orgId): array
  {
    $data = $this->model->where('org_id', $orgId)->findAll();

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
  }

  /**
   * Create a new tenant
   *
   * @param array $data
   * @return int|false|string
   */
  public function create(array $data): bool|int|string
  {
    return $this->model->insert($data, true);
  }

  /**
   * Update a tenant
   *
   * @param int $id
   * @param array $data
   * @return bool
   */
  public function update(int $id, array $data): bool
  {
    return $this->model->update($id, $data);
  }

  /**
   * Delete a tenant
   *
   * @param int $id
   * @return bool
   */
  public function delete(int $id): bool
  {
    return $this->model->delete($id);
  }

  /**
   * Get all tenants
   *
   * @return array
   */
  public function findAll(): array
  {
    $data = $this->model->findAll();

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
  }

  /**
   * Get count all
   *
   * @return int|string
   */
  public function countAll(): int|string
  {
    return $this->model->countAllResults();
  }


  /**
   * Get validation rules
   *
   * @return array
   */
  public function getValidationRules(): array
  {
    return $this->model->getValidationRules();
  }

  /**
   * Get validation errors from the model
   *
   * @return array
   */
  public function getErrors(): array
  {
    return $this->model->errors();
  }
}
