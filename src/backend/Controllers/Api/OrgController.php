<?php

namespace App\Controllers\Api;

use App\Services\OrgService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;

/**
 * @OA\Tag(
 *     name="Organization",
 *     description="Operations about organizations"
 * )
 * @OA\Schema(
 *     schema="Organization",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Organization Name"),
 *     @OA\Property(property="slug", type="string", example="organization-name"),
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
 *     schema="OrganizationInput",
 *     type="object",
 *     required={"slug", "name"},
 *     @OA\Property(property="slug", type="string", example="new-organization", description="Unique identifier for the organization. Must be at least 3 characters, contain only alphanumeric characters, underscores, and dashes."),
 *     @OA\Property(property="name", type="string", example="New Organization", description="Display name for the organization. Must be at least 3 characters.")
 * )
 */
class OrgController extends ResourceController
{
  use ResponseTrait;

  protected $orgService;
  protected $format = 'json';

  public function __construct()
  {
    $this->orgService = new OrgService();
  }

  /**
   * @OA\Get(
   *     path="/orgs",
   *     tags={"Organization"},
   *     summary="Get paginated list of organizations",
   *     security={{"bearerAuth":{}}},
   *     operationId="getOrganizations",
   *     @OA\Parameter(
   *         name="limit",
   *         in="query",
   *         description="Number of records to return",
   *         required=false,
   *         @OA\Schema(type="integer", default=20)
   *     ),
   *     @OA\Parameter(
   *         name="offset",
   *         in="query",
   *         description="Number of records to skip",
   *         required=false,
   *         @OA\Schema(type="integer", default=0)
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Organization")),
   *             @OA\Property(
   *                 property="paging",
   *                 type="object",
   *                 @OA\Property(property="total", type="integer", example=100),
   *                 @OA\Property(property="limit", type="integer", example=20),
   *                 @OA\Property(property="offset", type="integer", example=0)
   *             )
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
      $data = $this->orgService->getPaginated($limit, $offset);
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/orgs/all",
   *     tags={"Organization"},
   *     summary="Get all organizations",
   *     security={{"bearerAuth":{}}},
   *     operationId="getAllOrganizations",
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Organization"))
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function all(): ResponseInterface
  {
    try {
      $data = $this->orgService->getAll();
      return $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Get(
   *     path="/orgs/{id}",
   *     tags={"Organization"},
   *     summary="Get organization by ID",
   *     security={{"bearerAuth":{}}},
   *     operationId="getOrganizationById",
   *     @OA\Parameter(name="id", in="path", description="Organization ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *         @OA\JsonContent(ref="#/components/schemas/Organization")
   *     ),
   *     @OA\Response(response=404, description="Organization not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Organization not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function show($id = null): ResponseInterface
  {
    try {
      $data = $this->orgService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Post(
   *     path="/orgs",
   *     tags={"Organization"},
   *     summary="Create a new organization",
   *     security={{"bearerAuth":{}}},
   *     operationId="createOrganization",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/OrganizationInput")
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Organization created successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Organization")
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(
   *                 property="errors",
   *                 type="object",
   *                 @OA\Property(property="slug", type="string", example="The slug field is required."),
   *                 @OA\Property(property="name", type="string", example="The name field is required.")
   *             )
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function create(): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $id = $this->orgService->create($data);
      if (!$id) {
        return $this->failValidationErrors($this->orgService->getErrors());
      }
      $data = $this->orgService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Put(
   *     path="/orgs/{id}",
   *     tags={"Organization"},
   *     summary="Update an organization",
   *     security={{"bearerAuth":{}}},
   *     operationId="updateOrganization",
   *     @OA\Parameter(name="id", in="path", description="Organization ID", required=true, @OA\Schema(type="integer")),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/OrganizationInput")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Organization updated successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Organization")
   *     ),
   *     @OA\Response(response=404, description="Organization not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Organization not found"))),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(
   *                 property="errors",
   *                 type="object",
   *                 @OA\Property(property="slug", type="string", example="The slug must be at least 3 characters."),
   *                 @OA\Property(property="name", type="string", example="The name must be at least 3 characters.")
   *             )
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function update($id = null): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $result = $this->orgService->update($id, $data);
      if (!$result) {
        return $this->failValidationErrors($this->orgService->getErrors());
      }
      $data = $this->orgService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respondUpdated($data);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Delete(
   *     path="/orgs/{id}",
   *     tags={"Organization"},
   *     summary="Delete an organization",
   *     security={{"bearerAuth":{}}},
   *     operationId="deleteOrganization",
   *     @OA\Parameter(name="id", in="path", description="Organization ID", required=true, @OA\Schema(type="integer")),
   *     @OA\Response(
   *         response=200,
   *         description="Org deleted successfully",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1)
   *         )
   *     ),
   *     @OA\Response(response=404, description="Organization not found", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Organization not found"))),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function delete($id = null): ResponseInterface
  {
    try {
      $result = $this->orgService->delete($id);
      return (!$result) ? $this->failNotFound() : $this->respondDeleted(['id' => (int)$id]);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }
}