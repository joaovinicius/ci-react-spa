<?php

use CodeIgniter\Router\RouteCollection;

use App\Controllers\Api\SwaggerController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\OrgController;
use App\Controllers\Api\TenantController;
use App\Controllers\Api\UserController;
use App\Controllers\PageController;

/**
 * @var RouteCollection $routes
 */

$routes->group('api', ['filter' => 'cors:api'], static function (RouteCollection $routes): void {
  $routes->options('(:any)', static function () {
    return response();
  });

  // Swagger
  $routes->get('v1/docs/generate', [SwaggerController::class, 'generate']);
  $routes->get('v1/docs/ui', [SwaggerController::class, 'index']);

  // Auth public
  $routes->post('auth/login', [AuthController::class, 'login']);
  $routes->post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
  $routes->post('auth/verify-reset-token', [AuthController::class, 'verifyResetToken']);
  $routes->post('auth/reset-password', [AuthController::class, 'resetPassword']);
  $routes->post('auth/register', [AuthController::class, 'register']);

  $routes->group('', ['filter' => ['jwt', 'adminOnly']], static function ($routes) {
    // Auth
    $routes->get('auth/me', [AuthController::class, 'me']);
    $routes->get('auth/logout', [AuthController::class, 'logout']);
    $routes->post('auth/refresh-token', [AuthController::class, 'refreshToken']);

    // Org CRUD routes
    $routes->get('orgs', [OrgController::class, 'index']);
    $routes->get('orgs/all', [OrgController::class, 'all']);
    $routes->get('orgs/(:segment)', [OrgController::class, 'show/$1']);
    $routes->get('orgs/slug/(:segment)', [OrgController::class, 'getBySlug/$1']);
    $routes->post('orgs', [OrgController::class, 'create']);
    $routes->put('orgs/(:segment)', [OrgController::class, 'update/$1']);
    $routes->delete('orgs/(:segment)', [OrgController::class, 'delete/$1']);

    // Tenant CRUD routes
    $routes->get('tenants', [TenantController::class, 'index']);
    $routes->get('tenants/all', [TenantController::class, 'all']);
    $routes->get('tenants/(:segment)', [TenantController::class, 'show/$1']);
    $routes->get('tenants/domain/(:segment)', [TenantController::class, 'getByDomain/$1']);
    $routes->get('tenants/org/(:segment)', [TenantController::class, 'getByOrgId/$1']);
    $routes->post('tenants', [TenantController::class, 'create']);
    $routes->put('tenants/(:segment)', [TenantController::class, 'update/$1']);
    $routes->delete('tenants/(:segment)', [TenantController::class, 'delete/$1']);

    // User CRUD routes
    $routes->get('users', [UserController::class, 'index']);
    $routes->get('users/all', [UserController::class, 'all']);
    $routes->get('users/(:segment)', [UserController::class, 'show/$1']);
    $routes->get('users/org/(:segment)', [UserController::class, 'getByOrgId/$1']);
    $routes->post('users', [UserController::class, 'create']);
    $routes->put('users/(:segment)', [UserController::class, 'update/$1']);
    $routes->delete('users/(:segment)', [UserController::class, 'delete/$1']);
    $routes->get('users/tenant/(:segment)', [UserController::class, 'getByTenantId/$1']);

  });
});

$routes->get('(:any)', [PageController::class, 'index'], ['priority' => 1]);