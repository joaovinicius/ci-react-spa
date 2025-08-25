<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAcceptFilter implements FilterInterface
{
  /**
   * Filter for API requests to ensure they have application/json content type
   *
   * @param RequestInterface $request
   * @param array|null $arguments
   * @return mixed
   */
  public function before(RequestInterface $request, $arguments = null)
  {
    // Ignore methods sem corpo e preflight
    $method = strtolower($request->getMethod());
    if (in_array($method, ['get', 'delete', 'options', 'head'], true)) {
      return $request;
    }

    $accept = strtolower($request->getHeaderLine('accept') ?? '');

    // Aceita application/json, */* ou header ausente
    if ($accept === '' || strpos($accept, 'application/json') !== false || strpos($accept, '*/*') !== false) {
      return $request;
    }

    return service('response')
      ->setStatusCode(ResponseInterface::HTTP_UNSUPPORTED_MEDIA_TYPE)
      ->setJSON([
        'status' => 'error',
        'message' => 'Accept must include application/json',
      ]);
  }

  /**
   * Post-processing
   *
   * @param RequestInterface $request
   * @param ResponseInterface $response
   * @param array|null $arguments
   * @return mixed
   */
  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    return $response;
  }
}
