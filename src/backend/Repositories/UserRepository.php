<?php

namespace App\Repositories;

use App\Models\UserModel;

class UserRepository
{
  protected $model;

  public function __construct()
  {
    $this->model = new UserModel();
  }

  /**
   * Get paginated users
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
   * Get a user by ID
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
   * Get a user by email
   *
   * @param string $email
   * @return array|null|object
   */
  public function findByEmail(string $email): array|null|object
  {
    $data = $this->model->where('email', $email)->first();

    return $data?->toPublicArray();
  }


  /**
   * Get users by organization ID
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
   * Get users by tenant ID
   *
   * @param int $tenantId
   * @return array
   */
  public function getByTenantId(int $tenantId): array
  {
    $data = $this->model->where('tenant_id', $tenantId)->findAll();

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
  }


  /**
   * Create a new user
   *
   * @param array $data
   * @return bool|int|string
   */
  public function create(array $data): bool|int|string
  {
    return $this->model->insert($data, true);
  }

  /**
   * Update a user
   *
   * @param int $id
   * @param array $data
   * @return bool
   */
  public function update(int $id, array $data): bool
  {
    return $this->model->update($id, [
      $data
    ]);
  }

  /**
   * Delete a user
   *
   * @param int $id
   * @return bool
   */
  public function delete(int $id): bool
  {
    return $this->model->delete($id);
  }

  /**
   * Get all users
   *
   * @return array
   */
  public function findAll(): array
  {
    $data = $this->model->findAll();

    return $data ? array_map(fn($entity) => $entity->toPublicArray(), $data) : [];
  }

  /**
   * Verify user password
   *
   * @param string $email
   * @param string $password
   * @return bool
   */
  public function verifyPassword(string $email, string $password): bool
  {
    $user = $this->model->where('email', $email)->first();

    if (!$user) {
      return false;
    }

    return $user->verifyPassword($password);
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
