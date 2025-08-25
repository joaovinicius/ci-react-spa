<?php

namespace App\HTTP;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;

/**
 * Request personalizado com suporte a usuário autenticado.
 */
class Request extends IncomingRequest
{
    /**
     * Armazena o usuário autenticado para o ciclo desta requisição.
     * Estrutura a seu critério (array, DTO, etc.). Aqui usamos array|null.
     */
    protected ?array $authenticatedUser = null;

    public function __construct(App $config, ?URI $uri = null, string $body = 'php://input', ?UserAgent $userAgent = null)
    {
        parent::__construct($config, $uri, $body, $userAgent);
    }

    /**
     * Define o usuário autenticado para esta requisição.
     *
     * @return $this
     */
    public function setUser(array $user): self
    {
        $this->authenticatedUser = $user;
        return $this;
    }

    /**
     * Retorna o usuário autenticado (ou null se não houver).
     */
    public function getUser(): ?array
    {
        return $this->authenticatedUser;
    }
}
