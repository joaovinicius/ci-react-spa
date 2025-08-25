<?php

namespace App\Controllers;

class PageController extends BaseController
{
    public function index()
    {
        $path = FCPATH . 'index.html';
        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $body = file_get_contents($path);
        return $this->response->setContentType('text/html')->setBody($body);
    }
}