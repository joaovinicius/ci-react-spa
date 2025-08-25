<?php

namespace App\Controllers\Api;

use App\Services\UserService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Operations about users"
 * )
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="phone", type="string", example="+1234567890", nullable=true),
 *     @OA\Property(property="bio", type="string", example="User biography", nullable=true),
 *     @OA\Property(property="email_verified", type="boolean", example=true),
 *     @OA\Property(property="org_id", type="integer", example=1),
 *     @OA\Property(property="tenant_id", type="integer", example=1),
 *     @OA\Property(property="role", type="array", @OA\Items(type="string"), example={"User", "OrgAdmin"}),
 *     @OA\Property(
 *         property="created_at",
 *         type="object",
 *         @OA\Property(property="date", type="string", example="2025-06-08 02:13:02.000000"),
 *         @OA\Property(property="timezone_type", type="integer", example=3),
 *         @OA\Property(property="timezone", type="string", example="UTC")
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="object",
 *         @OA\Property(property="date", type="string", example="2025-06-08 02:13:02.000000"),
 *         @OA\Property(property="timezone_type", type="integer", example=3),
 *         @OA\Property(property="timezone", type="string", example="UTC")
 *     )
 * )
 * @OA\Schema(
 *     schema="UserInput",
 *     type="object",
 *     required={"email", "name", "password"},
 *     @OA\Property(property="email", type="string", example="user@example.com", description="User email address. Must be unique."),
 *     @OA\Property(property="name", type="string", example="John Doe", description="User's full name. Must be at least 3 characters."),
 *     @OA\Property(property="password", type="string", example="securepassword", description="User password. Must be at least 8 characters."),
 *     @OA\Property(property="phone", type="string", example="+1234567890", description="User phone number.", nullable=true),
 *     @OA\Property(property="bio", type="string", example="User biography", description="User biography or description.", nullable=true),
 *     @OA\Property(property="email_verified", type="boolean", example=false, description="Whether the email is verified."),
 *     @OA\Property(property="org_id", type="integer", example=1, description="Organization ID the user belongs to."),
 *     @OA\Property(property="tenant_id", type="integer", example=1, description="Tenant ID the user belongs to."),
 *     @OA\Property(property="role", type="array", @OA\Items(type="string"), example={"User"})
 * )
 */
class UserController extends ResourceController
{
  use ResponseTrait;

  protected $userService;
  protected $format = 'json';

  public function __construct()
  {
    $this->userService = new UserService();
  }

  /**
   * @OA\Get(
   *     path="/users",
   *     tags={"User"},
   *     summary="Get paginated list of users",
   *     security={{"bearerAuth":{}}},
   *     operationId="getUsers",
   *     @OA\Parameter(name="limit", in="query", description="Number of records to return", required=false, @OA\Schema(type="integer", default=20)),
   *     @OA\Parameter(name="offset", in="query", description="Number of records to skip", required=false, @OA\Schema(type="integer", default=0)),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
   *             @OA\Property(property="paging", type="object", @OA\Property(property="total", type="integer", example=100), @OA\Property(property="limit", type="integer", example=20), @OA\Property(property="offset", type="integer", example=0))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function index(): ResponseInterface
  {
    try {
      $limit = $this->request->getGet('limit') ?? 20;
      $offset = $this->request->getGet('offset') ?? 0;
      $data = $this->userService->getPaginated($limit, $offset);
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/users/all",
   *     tags={"User"},
   *     summary="Get all users",
   *     security={{"bearerAuth":{}}},
   *     operationId="getAllUsers",
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function all(): ResponseInterface
  {
    try {
      $data = $this->userService->getAll();
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/users/org/{orgId}",
   *     tags={"User"},
   *     summary="Get paginated list of users by Organization ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getUsersByOrgId",
   *     @OA\Parameter(name="orgId", in="path", description="Organization ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Parameter(name="limit", in="query", description="Number of records to return", required=false, @OA\Schema(type="integer", default=20)),
   *     @OA\Parameter(name="offset", in="query", description="Number of records to skip", required=false, @OA\Schema(type="integer", default=0)),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
   *             @OA\Property(property="paging", type="object", @OA\Property(property="total", type="integer", example=50), @OA\Property(property="limit", type="integer", example=20), @OA\Property(property="offset", type="integer", example=0))
   *         )
   *     ),
   *     @OA\Response(response=404, description="Organization not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="No users found for this organization or organization does not exist."))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function getByOrgId($orgId = null): ResponseInterface
  {
    try {
      $limit = $this->request->getGet('limit') ?? 20;
      $offset = $this->request->getGet('offset') ?? 0;
      $data = $this->userService->getByOrgId($orgId, $limit, $offset);
      return (!$data) ? $this->failNotFound('No users found for this organization or organization does not exist.') : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/users/{id}",
   *     tags={"User"},
   *     summary="Get user by ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getUserById",
   *     @OA\Parameter(name="id", in="path", description="User ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(ref="#/components/schemas/User")),
   *     @OA\Response(response=404, description="User not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="User not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function show($id = null): ResponseInterface
  {
    try {
      $data = $this->userService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Post(
   *     path="/users",
   *     tags={"User"},
   *     summary="Create a new user",
   *     security={{"bearerAuth":{}}},
   *     operationId="createUser",
   *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserInput")),
   *     @OA\Response(response=201, description="User created successfully", @OA\JsonContent(ref="#/components/schemas/User")),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="errors", type="object", @OA\Property(property="email", type="string", example="The email field is required."), @OA\Property(property="name", type="string", example="The name field is required."), @OA\Property(property="password", type="string", example="The password field is required."))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function create(): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $id = $this->userService->create($data);
      if (!$id) {
        return $this->failValidationErrors($this->userService->getErrors());
      }
      $data = $this->userService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data, 201);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Put(
   *     path="/users/{id}",
   *     tags={"User"},
   *     summary="Update a user",
   *     security={{"bearerAuth":{}}},
   *     operationId="updateUser",
   *     @OA\Parameter(name="id", in="path", description="User ID", required=true, @OA\Schema(type="integer")),
   *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserInput")),
   *     @OA\Response(response=200, description="User updated successfully", @OA\JsonContent(ref="#/components/schemas/User")),
   *     @OA\Response(response=404, description="User not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="User not found"))),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="errors", type="object", @OA\Property(property="email", type="string", example="The email must be a valid email address."), @OA\Property(property="name", type="string", example="The name must be at least 3 characters."))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function update($id = null): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $result = $this->userService->update($id, $data);
      if (!$result) {
        return $this->failValidationErrors($this->userService->getErrors());
      }
      $data = $this->userService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respondUpdated($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Delete(
   *     path="/users/{id}",
   *     tags={"User"},
   *     summary="Delete a user",
   *     security={{"bearerAuth":{}}},
   *     operationId="deleteUser",
   *     @OA\Parameter(name="id", in="path", description="User ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(
   *         response=200,
   *         description="User deleted successfully",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1)
   *         )
   *     ),
   *     @OA\Response(response=404, description="User not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="User not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function delete($id = null): ResponseInterface
  {
    try {
      if (!$this->userService->delete($id)) {
        return $this->failNotFound();
      }
      return $this->respondDeleted(['id' => (int)$id]);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/users/tenant/{tenantId}",
   *     tags={"User"},
   *      summary="Get users by Tenant ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getUsersByTenantId",
   *     @OA\Parameter(name="tenantId", in="path", description="Tenant ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function getByTenantId($tenantId = null): ResponseInterface
  {
    try {
      $data = $this->userService->getByTenantId($tenantId);
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }
}