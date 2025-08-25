<?php

namespace App\Filters;

use App\Services\AuthService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtAuthFilter implements FilterInterface
{
  use ResponseTrait;

  public $response;

  public function before(RequestInterface $request, $arguments = null)
  {
    // Libera preflight
    if (strtolower($request->getMethod()) === 'options') {
      return $request;
    }

    $this->response = service('response');
    $token = $request->getHeaderLine('Authorization');

    if (!$token) {
      return $this->failUnauthorized();
    }
    
    if (preg_match('/Bearer\s+(.*)$/i', $token, $matches)) {
      $token = $matches[1];
    }

    $authService = new AuthService();
    $user = $authService->validateToken($token);

    if (!$user) {
      return $this->failUnauthorized();
    }

    if (method_exists($request, 'setUser')) {
      $request->setUser($user);
    }

    return $request;
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
  }
}