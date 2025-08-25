<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Exception;

class UserService
{
  protected $repository;

  public function __construct()
  {
    $this->repository = new UserRepository();
  }

  /**
   * Get all users
   *
   * @return array
   */
  public function getAll(): array
  {
    return $this->repository->findAll();
  }

  /**
   * Get paginated users
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
   * Get a user by ID
   *
   * @param int $id
   * @return array|null
   */
  public function getById(int $id): ?array
  {
    return $this->repository->find($id);
  }

  /**
   * Get a user by email
   *
   * @param string $email
   * @return array|null
   */
  public function getByEmail(string $email): ?array
  {
    return $this->repository->findByEmail($email);
  }

  /**
   * Get users by organization ID
   *
   * @param int $orgId
   * @return array
   */
  public function getByOrgId(int $orgId): array
  {
    return $this->repository->findByOrgId($orgId);
  }

  /**
   * Get users by tenant ID
   *
   * @param int $tenantId
   * @return array
   */
  public function getByTenantId(int $tenantId): array
  {
    return $this->repository->getByTenantId($tenantId);
  }

  /**
   * Create a new user
   *
   * @param array $data
   * @return false|int
   */
  public function create(array $data): false|int
  {
    return $this->repository->create($data);
  }

  /**
   * Update user
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
   * Delete a user
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
   * Verify user password
   *
   * @param string $email
   * @param string $password
   * @return bool
   */
  public function verifyPassword(string $email, string $password): bool
  {
    return $this->repository->verifyPassword($email, $password);
  }

  public function updatePassword(int $id, string $password): bool
  {
    return $this->repository->update($id, ['password' => $password]);
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
