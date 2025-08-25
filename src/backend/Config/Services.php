<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\UserAgent;
use Config\App as AppConfig;
use App\HTTP\Request as AppRequest;

class Services extends BaseService
{
  /**
   * Return the Request instance
   *
   * @param AppConfig|null $config
   */
  public static function request(AppConfig $config = null, bool $getShared = true)
  {
    if ($getShared) {
      return static::getSharedInstance('request', $config);
    }

    $config ??= config(AppConfig::class);

    // Obtenha o URI do serviço (SiteURI/URI corretos para roteamento)
    $uri = static::uri();

    // Garanta que o UserAgent nunca seja NULL
    $ua = static::userAgent();
    if (! $ua instanceof UserAgent) {
      $ua = new UserAgent();
    }

    return new AppRequest($config, $uri, 'php://input', $ua);
  }

  /**
   * Rewrite the service incomingrequest for custom Request.
   *
   * @param AppConfig|null $config
   */
  public static function incomingrequest(AppConfig $config = null, bool $getShared = true)
  {
    if ($getShared) {
      return static::getSharedInstance('incomingrequest', $config);
    }

    $config ??= config(AppConfig::class);

    $uri = static::uri();

    $ua = static::userAgent();
    if (! $ua instanceof UserAgent) {
      $ua = new UserAgent();
    }

    return new AppRequest($config, $uri, 'php://input', $ua);
  }

  /**
   * Force the custom "request".
   */
  public static function createRequest(AppConfig $config, bool $isCli = false): void
  {
    if ($isCli) {
      $request = static::clirequest($config);
    } else {
      $request = static::incomingrequest($config);
      $request->setProtocolVersion($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
    }

    static::$instances['request'] = $request;
  }

  /**
   * CLI Request.
   */
  public static function clirequest(AppConfig $config = null, bool $getShared = true)
  {
    if ($getShared) {
      return static::getSharedInstance('clirequest', $config);
    }

    $config ??= config(AppConfig::class);

    return new CLIRequest($config);
  }
}
