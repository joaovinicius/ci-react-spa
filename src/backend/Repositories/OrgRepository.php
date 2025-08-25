<?php

namespace App\Repositories;

use App\Models\OrgModel;

class OrgRepository
{
  protected $model;

  public function __construct()
  {
    $this->model = new OrgModel();
  }

  /**
   * Get paginated organizations
   *
   * @return array
   */
  public function findPaginated(int $limit = 0, int $offset = 0): array
  {
    $data = $this->model->findAll($limit, $offset);

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
   * Get an organization by ID
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
   * Get an organization by slug
   *
   * @param string $slug
   * @return array|null|object
   */
  public function findBySlug(string $slug): array|null|object
  {
    $data = $this->model->where('slug', $slug)->first();

    return $data?->toPublicArray();
  }

  /**
   * Create a new organization
   *
   * @param array $data
   * @return bool|int|string
   */
  public function create(array $data): bool|int|string
  {
    return $this->model->insert($data, true);
  }

  /**
   * Update an organization
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
   * Delete an organization
   *
   * @param int $id
   * @return bool
   */
  public function delete(int $id): bool
  {
    return $this->model->delete($id);
  }


  /**
   * Get all organizations
   *
   * @return array
   */
  public function findAll(): array
  {
    $data = $this->model->findAll();

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
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
