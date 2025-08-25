<?php

namespace App\Services;

use App\Repositories\TenantRepository;

class TenantService
{
  protected $repository;

  public function __construct()
  {
    $this->repository = new TenantRepository();
  }

  /**
   * Get all tenants
   *
   * @return array
   */
  public function getAll(): array
  {
    return $this->repository->findAll();
  }

  /**
   * Get tenants with pagination
   *
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function getPaginated(int $limit = 0, int $offset = 0): array
  {
    $data['data'] = $this->repository->findPaginated($limit, $offset);

    $data['paging'] = [
      'total' => $this->repository->countAll(),
      'limit' => $limit,
      'offset' => $offset,
    ];

    return $data;
  }

  /**
   * Get a tenant by ID
   *
   * @param int $id
   * @return array|null|object
   */
  public function getById(int $id): array|null|object
  {
    return $this->repository->find($id);
  }

  /**
   * Get a tenant by domain
   *
   * @param string $domain
   * @return object
   */
  public function getByDomain(string $domain): array|null|object
  {
    return $this->repository->findByDomain($domain);
  }

  /**
   * Get tenants by organization ID
   *
   * @param int $orgId
   * @return array
   */
  public function getByOrgId(int $orgId): array
  {
    return $this->repository->findByOrgId($orgId);
  }

  /**
   * Create a new tenant
   *
   * @param array $data
   * @return false|int|string
   */
  public function create(array $data): false|int|string
  {
    return $this->repository->create($data);
  }

  /**
   * Update tenant
   *
   * @param int $id
   * @param array $data
   * @return bool
   */
  public function update(int $id, array $data): bool
  {
    return $this->repository->update($id, $data);
  }

  /**
   * Delete a tenant
   *
   * @param int $id
   * @return bool
   */
  public function delete(int $id): bool
  {
    return $this->repository->delete($id);
  }

  /**
   * Get validation rules
   *
   * @return array
   */
  public function getValidationRules(): array
  {
    return $this->repository->getValidationRules();
  }

  /**
   * Get validation errors from the model
   *
   * @return array
   */
  public function getErrors(): array
  {
    return $this->repository->getErrors();
  }
}
