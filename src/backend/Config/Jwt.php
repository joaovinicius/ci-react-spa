<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jwt extends BaseConfig
{
  public string $key;
  public string $algorithm = 'HS256';
  public int $ttl = 3600; // Token time to live in seconds (1 hour)
  public array $allowedAlgorithms = ['HS256']; // restrinja algs aceitos
  public int $accessTtl = 900;                 // 15 min
  public int $refreshTtl = 1209600;            // 14 dias
  public int $leeway = 45;                     // segundos de tolerância para clock skew
  public string $issuer = 'https://api.seu-dominio.com';   // ajuste ao seu domínio
  public string $audience = 'https://seu-cliente.com';     // ajuste ao seu cliente
  // Suporte à rotação de chaves HMAC (kid => secret)
  public ?string $currentKid = null;           // kid atual usado para assinar
  public array $keys = [];                     // ['kid1' => 'secret1', 'kid2' => 'secret2']

  public function __construct()
  {
    parent::__construct();

    $envKey = getenv('encryption.key');
    if ($envKey === false || $envKey === '') {
      throw new \RuntimeException('JWT secret key not configured (encryption.key).');
    } else {
      $this->key = $envKey;
    }

    $keysJson = getenv('JWT_KEYS') ?: '';
    if ($keysJson !== '') {
      $decoded = json_decode($keysJson, true);
      if (is_array($decoded) && count($decoded) > 0) {
        $this->keys = $decoded;
        // Defina o KID atual por env para assinar novos tokens
        $this->currentKid = getenv('JWT_CURRENT_KID') ?: array_key_first($decoded);
      }
    }

    // Backward-compat: se não houver rotação configurada, use a chave única
    if (empty($this->keys)) {
      $this->keys = ['default' => $this->key];
      $this->currentKid = 'default';
    }
  }
}