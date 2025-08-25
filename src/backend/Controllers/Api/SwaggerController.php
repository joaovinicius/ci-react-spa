<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use OpenApi\Generator;

/**
 * @OA\OpenApi(
 *     openapi="3.1.0",
 *     @OA\Info(
 *         title="CMS",
 *         version="1.0.0",
 *         description="CMS endpoints"
 *     ),
 *     @OA\Server(
 *         url="http://localhost/api",
 *         description="CMS - Admin API server"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT",
 *             description="Enter token in format (Bearer <token>)"
 *         )
 *     )
 * )
 */
class SwaggerController extends BaseController
{
  public function generate()
  {
    $openapi = Generator::scan([APPPATH . 'Controllers']);
    return $this->response->setJSON($openapi->toJson());
  }

  public function index()
  {
    return view('swagger');
  }
}