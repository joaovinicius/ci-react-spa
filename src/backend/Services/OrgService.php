<?php

namespace App\Services;

use App\Repositories\OrgRepository;
use Exception;

class OrgService
{
  protected $repository;

  public function __construct()
  {
    $this->repository = new OrgRepository();
  }

  /**
   * Get all organizations
   *
   * @return array
   */
  public function getAll(): array
  {
    return $this->repository->findAll();
  }


  /**
   * Get all organizations
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
   * Get an organization by ID
   *
   * @param int $id
   * @return object|null
   */
  public function getById(int $id): null|objec
  {
    return $this->repository->find($id);
  }

  /**
   * Get an organization by slug
   *
   * @param string $slug
   * @return object|null
   */
  public function getBySlug(string $slug): null|objec
  {
    return $this->repository->findBySlug($slug);
  }

  /**
   * Create a new organization
   *
   * @param array $data
   * @return false|int
   */
  public function create(array $data): false|int
  {
    return $this->repository->create($data);
  }

  /**
   * Update organization
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
   * Delete an organization
   *
   * @param int $id
   * @return bool
   * @throws Exception
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
