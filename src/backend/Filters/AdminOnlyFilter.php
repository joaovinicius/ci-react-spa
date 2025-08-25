<?php

namespace App\Filters;

use App\Enums\Role;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminOnlyFilter implements FilterInterface
{
  use ResponseTrait;

  public $response;

  public function before(RequestInterface $request, $arguments = null)
  {
    $user = method_exists($request, 'getUser') ? $request->getUser() : null;

    if (!$user || !isset($user['role']) || !in_array(Role::Admin->value, (array) $user['role'], true)) {
      $this->response = service('response');
      return $this->failUnauthorized();
    }

    return $request;
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
  }
}