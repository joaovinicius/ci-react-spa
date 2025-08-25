<?php

namespace App\Controllers\Api;

use App\Services\TenantService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;

/**
 * @OA\Tag(
 *     name="Tenant",
 *     description="Operations about tenants"
 * )
 *
 * @OA\Schema(
 *     schema="Tenant",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Tenant Name"),
 *     @OA\Property(property="domain", type="string", example="https://example-tenant.com"),
 *     @OA\Property(
 *          property="config",
 *          type="object",
 *          example={"theme": "dark", "locale": "en"}
 *     ),
 *     @OA\Property(property="status", type="string", example="draft"),
 *     @OA\Property(property="org_id", type="integer", example=42),
 *     @OA\Property(
 *          property="created_at",
 *          type="object",
 *          @OA\Property(property="date", type="string", example="2025-06-08 02:13:02.000000"),
 *          @OA\Property(property="timezone_type", type="integer", example=3),
 *          @OA\Property(property="timezone", type="string", example="UTC")
 *     ),
 *     @OA\Property(
 *          property="updated_at",
 *          type="object",
 *          @OA\Property(property="date", type="string", example="2025-06-08 02:13:02.000000"),
 *          @OA\Property(property="timezone_type", type="integer", example=3),
 *          @OA\Property(property="timezone", type="string", example="UTC")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TenantInput",
 *     type="object",
 *     required={"domain", "name", "status", "config", "org_id"},
 *     @OA\Property(
 *         property="domain",
 *         type="string",
 *         description="Tenant domain (valid URL). Must be at least 3 characters and unique.",
 *         example="https://example-tenant.com"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Display name for the tenant. Must be at least 3 characters.",
 *         example="New Tenant"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"draft", "published", "archived"},
 *         description="Status of the tenant.",
 *         example="draft"
 *     ),
 *     @OA\Property(
 *         property="config",
 *         type="object",
 *         description="Configuration in JSON format.",
 *         example={"theme":"dark","locale":"en"}
 *     ),
 *     @OA\Property(
 *         property="org_id",
 *         type="integer",
 *         description="ID of the associated organization.",
 *         example=42
 *     )
 * )
 */
class TenantController extends ResourceController
{
  use ResponseTrait;

  protected $tenantService;
  protected $format = 'json';

  public function __construct()
  {
    $this->tenantService = new TenantService();
  }

  /**
   * @OA\Get(
   *     path="/tenants",
   *     tags={"Tenant"},
   *     summary="Get paginated list of tenants",
   *     security={{"bearerAuth":{}}},
   *     operationId="getTenants",
   *     @OA\Parameter(name="limit", in="query", description="Number of records to return", required=false, @OA\Schema(type="integer", default=20)),
   *     @OA\Parameter(name="offset", in="query", description="Number of records to skip", required=false, @OA\Schema(type="integer", default=0)),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Tenant")),
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
      $data = $this->tenantService->getPaginated($limit, $offset);
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/tenants/all",
   *     tags={"Tenant"},
   *     summary="Get all tenants",
   *     security={{"bearerAuth":{}}},
   *     operationId="getAllTenants",
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Tenant"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function all(): ResponseInterface
  {
    try {
      $data = $this->tenantService->getAll();
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/tenants/{id}",
   *     tags={"Tenant"},
   *     summary="Get tenant by ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getTenantById",
   *     @OA\Parameter(name="id", in="path", description="Tenant ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(ref="#/components/schemas/Tenant")),
   *     @OA\Response(response=404, description="Tenant not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Tenant not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function show($id = null): ResponseInterface
  {
    try {
      $data = $this->tenantService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Post(
   *     path="/tenants",
   *     tags={"Tenant"},
   *     summary="Create a new tenant",
   *     security={{"bearerAuth":{}}},
   *     operationId="createTenant",
   *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantInput")),
   *     @OA\Response(response=201, description="Tenant created successfully", @OA\JsonContent(ref="#/components/schemas/Tenant")),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="errors", type="object", @OA\Property(property="slug", type="string", example="The slug field is required."), @OA\Property(property="name", type="string", example="The name field is required."))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function create(): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $id = $this->tenantService->create($data);
      if (!$id) {
        return $this->failValidationErrors($this->tenantService->getErrors());
      }
      $data = $this->tenantService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Put(
   *     path="/tenants/{id}",
   *     tags={"Tenant"},
   *     summary="Update a tenant",
   *     security={{"bearerAuth":{}}},
   *     operationId="updateTenant",
   *     @OA\Parameter(name="id", in="path", description="Tenant ID", required=true, @OA\Schema(type="integer")),
   *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantInput")),
   *     @OA\Response(response=200, description="Tenant updated successfully", @OA\JsonContent(ref="#/components/schemas/Tenant")),
   *     @OA\Response(response=404, description="Tenant not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Tenant not found"))),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="errors", type="object", @OA\Property(property="slug", type="string", example="The slug must be at least 3 characters."), @OA\Property(property="name", type="string", example="The name must be at least 3 characters."))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function update($id = null): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $result = $this->tenantService->update($id, $data);
      if (!$result) {
        return $this->failValidationErrors($this->tenantService->getErrors());
      }
      $data = $this->tenantService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respondUpdated($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Delete(
   *     path="/tenants/{id}",
   *     tags={"Tenant"},
   *     summary="Delete a tenant",
   *     security={{"bearerAuth":{}}},
   *     operationId="deleteTenant",
   *     @OA\Parameter(name="id", in="path", description="Tenant ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(
   *         response=200,
   *         description="Tenant deleted successfully",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1)
   *         )
   *     ),
   *     @OA\Response(response=404, description="Tenant not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Tenant not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function delete($id = null): ResponseInterface
  {
    try {
      if (!$this->tenantService->delete($id)) {
        return $this->failNotFound();
      }
      return $this->respondDeleted(['id' => (int)$id]);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/tenants/domain/{domain}",
   *     tags={"Tenant"},
   *     summary="Get tenant by domain",
   *     security={{"bearerAuth":{}}},
   *     operationId="getTenantByDomain",
   *     @OA\Parameter(name="domain", in="path", description="Tenant domain", required=true, @OA\Schema(type="string")),
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(ref="#/components/schemas/Tenant")),
   *     @OA\Response(response=404, description="Tenant not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Tenant not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function getByDomain($domain = null): ResponseInterface
  {
    try {
      $data = $this->tenantService->getByDomain($domain);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/tenants/org/{orgId}",
   *     tags={"Tenant"},
   *     summary="Get tenants by Organization ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getTenantsByOrgId",
   *     @OA\Parameter(name="orgId", in="path", description="Organization ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(response=200, description="Successful response", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Tenant"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function getByOrgId($orgId = null): ResponseInterface
  {
    try {
      $data = $this->tenantService->getByOrgId($orgId);
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }
}