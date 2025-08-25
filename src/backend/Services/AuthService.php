<?php

namespace App\Services;

use CodeIgniter\Email\Email;
use Config\Jwt;
use Config\Services;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

class AuthService
{
  protected UserService $userService;
  protected Jwt $jwtConfig;
  protected Email $emailService; // For sending emails

  // It's good practice to define a shorter TTL for reset tokens
  protected int $passwordResetTokenTtl = 900; // 15 minutes


  public function __construct()
  {
    $this->userService = new UserService();
    $this->jwtConfig = config('Jwt');
    $this->emailService = Services::email();
    FirebaseJWT::$leeway = $this->jwtConfig->leeway;
  }

  /**
   * Tenta autenticar um usuário e emitir tokens.
   *
   * @param string $email E-mail do usuário.
   * @param string $password Senha em texto puro.
   * @return array{
   *   access_token: string,
   *   refresh_token: string,
   *   token_type: 'Bearer',
   *   expires_in: int,
   *   user: array<string,mixed>
   * }|false Retorna um array com tokens e dados do usuário em caso de sucesso; false caso e-mail/senha inválidos.
   */
  public function attempt(string $email, string $password): array|false
  {
    if (!$this->userService->verifyPassword($email, $password)) {
      return false;
    }

    $user = $this->userService->getByEmail($email);
    if (!$user) {
      return false;
    }

    $tokens = $this->issueTokens($user);

    return [
      'access_token' => $tokens['access_token'],
      'refresh_token' => $tokens['refresh_token'],
      'token_type' => 'Bearer',
      'expires_in' => $this->jwtConfig->accessTtl,
      'user' => $user
    ];
  }

  /**
   * Emite um par de tokens (access e refresh) para o usuário informado.
   *
   * @param array<string,mixed> $user Dados do usuário (deve conter pelo menos 'id').
   * @return array{access_token: string, refresh_token: string} Tokens JWT assinados.
   */
  public function issueTokens(array $user): array
  {
    $now = time();
    return [
      'access_token' => $this->signJwt($user, $this->jwtConfig->accessTtl, [
        'purpose' => 'access',
        'nbf' => $now - 1, // tolerante a leve adiantamento de relógio
      ]),
      'refresh_token' => $this->signJwt($user, $this->jwtConfig->refreshTtl, [
        'purpose' => 'refresh',
        'nbf' => $now - 1,
      ]),
    ];
  }

  /**
   * Assina um JWT com as claims informadas.
   *
   * @param array<string,mixed> $user Dados do usuário (deve conter pelo menos 'id').
   * @param int $ttl Tempo de vida, em segundos.
   * @param array<string,mixed> $customClaims Claims adicionais.
   * @return string Token JWT assinado.
   */
  protected function signJwt(array $user, int $ttl, array $customClaims = []): string
  {
    $issuedAt = time();
    $expire = $issuedAt + $ttl;

    $payload = array_merge([
      'jti' => bin2hex(random_bytes(16)),
      'iat' => $issuedAt,
      'nbf' => $issuedAt,
      'exp' => $expire,
      'iss' => $this->jwtConfig->issuer,
      'aud' => $this->jwtConfig->audience,
      'sub' => (string) $user['id'],
      'uid' => $user['id'],
    ], $customClaims);

    $kid = $this->jwtConfig->currentKid ?? 'default';
    $secret = $this->jwtConfig->keys[$kid] ?? $this->jwtConfig->key;

    // Header com kid (e typ padrão JWT)
    return FirebaseJWT::encode(
      $payload,
      $secret,
      $this->jwtConfig->algorithm,
      $kid,
      ['typ' => 'JWT']
    );
  }

  /**
   * Gera um token genérico com claims customizáveis.
   *
   * @param array<string,mixed> $user Dados do usuário (deve conter 'id').
   * @param int|null $ttl Tempo de vida do token (segundos). Se null, usa config->ttl.
   * @param array<string,mixed> $customClaims Claims adicionais a incluir no token.
   * @return string Token JWT assinado.
   */
  public function generateToken(array $user, ?int $ttl = null, array $customClaims = []): string
  {
    $issuedAt = time();
    $expire = $issuedAt + ($ttl ?? $this->jwtConfig->ttl);

    $payload = array_merge([
      'iat' => $issuedAt,
      'exp' => $expire,
      'uid' => $user['id']
    ], $customClaims);

    return FirebaseJWT::encode($payload, $this->jwtConfig->key, $this->jwtConfig->algorithm);
  }

  /**
   * Valida um token e retorna os dados do usuário vinculado.
   *
   * @param string|null $token Token JWT a validar.
   * @param 'access'|'refresh'|null $requiredPurpose Se informado, exige que a claim "purpose" combine (ex.: 'access' ou 'refresh').
   * @return array<string,mixed>|null Array de dados do usuário quando válido; null quando inválido/expirado/incompatível.
   */
  public function validateToken(?string $token, ?string $requiredPurpose = 'access'): ?array
  {
    if (!$token) {
      return null;
    }

    try {
      // Tente validar contra qualquer chave configurada (suporte a rotação)
      $decoded = $this->decodeWithAnyKey($token);

      // Validações de conteúdo
      if (isset($decoded->iss) && $decoded->iss !== $this->jwtConfig->issuer) {
        return null;
      }
      if (isset($decoded->aud) && $decoded->aud !== $this->jwtConfig->audience) {
        return null;
      }
      if ($requiredPurpose !== null) {
        if (!isset($decoded->purpose) || $decoded->purpose !== $requiredPurpose) {
          return null;
        }
      }
      if (!isset($decoded->uid)) {
        return null;
      }

      return $this->userService->getById($decoded->uid);
    } catch (Exception $e) {
      return null;
    }
  }

  /**
   * Decodifica um JWT tentando todas as chaves configuradas (suporte à rotação).
   *
   * @param string $token Token JWT.
   * @return object Objeto decodificado do JWT.
   * @throws InvalidArgumentException Se o algoritmo configurado não for permitido.
   * @throws ExpiredException Se o token estiver expirado.
   * @throws SignatureInvalidException Se a assinatura for inválida.
   * @throws BeforeValidException Se o token ainda não for válido (nbf/iat).
   * @throws UnexpectedValueException Se não for possível decodificar com as chaves fornecidas.
   */
  protected function decodeWithAnyKey(string $token): object
  {
    $lastException = null;

    // Restringe os algoritmos aceitos
    $alg = $this->jwtConfig->algorithm;
    $allowed = $this->jwtConfig->allowedAlgorithms;
    if (!in_array($alg, $allowed, true)) {
      throw new InvalidArgumentException('Disallowed JWT algorithm.');
    }

    foreach ($this->jwtConfig->keys as $kid => $secret) {
      try {
        return FirebaseJWT::decode($token, new Key($secret, $alg));
      } catch (ExpiredException|SignatureInvalidException|BeforeValidException $e) {
        // exceções críticas: propaga a primeira crítica imediatamente para clareza
        $lastException = $e;
        throw $e;
      } catch (Exception $e) {
        // tenta próxima chave
        $lastException = $e;
        continue;
      }
    }

    if ($lastException instanceof Exception) {
      throw $lastException;
    }
    throw new UnexpectedValueException('Unable to decode JWT with provided keys.');
  }

  /**
   * Envia e-mail de redefinição de senha com o link contendo o token.
   *
   * @param array<string,mixed> $user Usuário destinatário.
   * @param string $token Token de redefinição.
   * @return bool true se o e-mail foi enfileirado/enviado; false em erro de envio.
   */
  protected function sendPasswordResetEmail(array $user, string $token): bool
  {
    $resetLink = rtrim(config('App')->baseURL, '/') . '/reset-password?token=' . $token;

    $this->emailService->setTo($user['email']);
    $this->emailService->setSubject('Password Reset Request');
    $this->emailService->setMessage(
      "Hello {$user['name']},\n\nPlease click the following link to reset your password:\n{$resetLink}\n\nIf you did not request a password reset, please ignore this email."
    );

    if ($this->emailService->send()) {
      log_message('info', 'Password reset email sent to: ' . $user['email']);
      return true;
    } else {
      log_message('error', 'Failed to send password reset email to: ' . $user['email'] . ' Error: ' . $this->emailService->printDebugger(['headers']));
      return false;
    }
  }

  /**
   * Processa a solicitação de "esqueci minha senha".
   *
   * Observação: Retorna sempre true para evitar enumeração de usuários.
   *
   * @param string $email E-mail informado.
   * @return bool true sempre, independentemente de o e-mail existir ou não.
   */
  public function handleForgotPasswordRequest(string $email): bool
  {
    $user = $this->userService->getByEmail($email);

    if ($user) {
      $resetToken = $this->generateToken($user, $this->passwordResetTokenTtl, ['purpose' => 'password_reset']);
      $this->sendPasswordResetEmail($user, $resetToken);
    } else {
      log_message('info', "Password reset requested for non-existent email: {$email}");
    }

    // Always return true to prevent user enumeration
    return true;
  }

  /**
   * Valida um token de redefinição de senha e retorna o usuário correspondente.
   *
   * @param string|null $token Token de redefinição.
   * @param bool $throwExceptions Quando true, lança exceções detalhadas em caso de falha.
   * @return array<string,mixed>|null Usuário quando válido; null quando inválido/expirado.
   * @throws InvalidArgumentException Quando o token não é fornecido e $throwExceptions = true.
   * @throws ExpiredException Quando o token está expirado e $throwExceptions = true.
   * @throws SignatureInvalidException Quando a assinatura é inválida e $throwExceptions = true.
   * @throws BeforeValidException Quando o token ainda não é válido e $throwExceptions = true.
   * @throws UnexpectedValueException Quando o conteúdo do token é inválido e $throwExceptions = true.
   * @throws RuntimeException Quando o usuário não é encontrado para o token e $throwExceptions = true.
   */
  public function validatePasswordResetToken(?string $token, bool $throwExceptions = false): ?array
  {
    if (!$token) {
      if ($throwExceptions) throw new InvalidArgumentException('Token not provided.');
      return null;
    }

    try {
      $decoded = FirebaseJWT::decode($token, new Key($this->jwtConfig->key, $this->jwtConfig->algorithm));

      if (!isset($decoded->purpose) || $decoded->purpose !== 'password_reset') {
        if ($throwExceptions) throw new UnexpectedValueException('Invalid token purpose.');
        log_message('info', 'Password reset token validation failed: Invalid purpose.');
        return null;
      }

      if (!isset($decoded->uid)) {
        if ($throwExceptions) throw new UnexpectedValueException('User ID missing in token.');
        log_message('info', 'Password reset token validation failed: Missing user ID.');
        return null;
      }
      $user = $this->userService->getById($decoded->uid);
      if (!$user) {
        if ($throwExceptions) throw new RuntimeException('User not found for token.');
        log_message('info', 'Password reset token validation failed: User not found.');
        return null;
      }
      return $user;

    } catch (ExpiredException $e) {
      if ($throwExceptions) throw $e;
      log_message('info', 'Password reset token validation failed: Expired token.');
      return null;
    } catch (SignatureInvalidException $e) {
      if ($throwExceptions) throw $e;
      log_message('info', 'Password reset token validation failed: Invalid signature.');
      return null;
    } catch (BeforeValidException $e) {
      if ($throwExceptions) throw $e;
      log_message('info', 'Password reset token validation failed: Token not yet valid.');
      return null;
    } catch (Exception $e) {
      if ($throwExceptions) throw $e;
      log_message('error', 'Password reset token validation error: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Redefine a senha do usuário a partir de um token válido.
   *
   * @param string $token Token de redefinição (deve ser válido).
   * @param string $newPassword Nova senha em texto puro.
   * @return bool true se a senha foi atualizada com sucesso; false em falha (token inválido/usuário não encontrado/erro de atualização).
   * @throws Exception Propaga exceções levantadas por validatePasswordResetToken quando em modo de exceção.
   */
  public function resetPassword(string $token, string $newPassword): bool
  {
    $user = $this->validatePasswordResetToken($token, true);

    if ($user && isset($user['id'])) {
      $success = $this->userService->updatePassword($user['id'], $newPassword);
      if ($success) {
        log_message('info', "Password reset successfully for user ID: {$user['id']}");
        return true;
      } else {
        log_message('error', "Failed to update password for user ID: {$user['id']} after token validation.");
        return false;
      }
    }
    return false;
  }

}