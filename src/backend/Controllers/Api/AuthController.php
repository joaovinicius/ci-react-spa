<?php

namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Services\UserService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication and password management"
 * )
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(property="token", type="string", description="JWT token for authentication"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */
class AuthController extends ResourceController
{
  use ResponseTrait;

  protected AuthService $authService;
  protected $format = 'json';

  public function __construct()
  {
    $this->userService = new UserService();
    $this->authService = new AuthService();
  }

  /**
   * @OA\Get(
   *     path="/auth/me",
   *     tags={"Auth"},
   *     summary="Return current user",
   *     security={{"bearerAuth":{}}},
   *     operationId="getCurrentUser",
   *     @OA\Response(response=200, description="User created successfully", @OA\JsonContent(ref="#/components/schemas/User")),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="integer", example=401),
   *             @OA\Property(property="error", type="integer", example=401),
   *             @OA\Property(
   *                 property="messages",
   *                 type="object",
   *                 @OA\Property(property="error", type="string", example="Unauthorized")
   *             )
   *         )
   *     ),
   * )
   */
  public function me(): ResponseInterface
  {
    return $this->respond($this->request->getUser());
  }

  /**
   * @OA\Post(
   *     path="/auth/login",
   *     tags={"Auth"},
   *     summary="Authenticate user and issue JWT tokens",
   *     operationId="verifyUserPassword",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"email", "password"},
   *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
   *             @OA\Property(property="password", type="string", format="password", example="securepassword")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Authentication successful",
   *         @OA\JsonContent(
   *              required={"access_token","refresh_token","token_type","expires_in","user"},
   *              type="object",
   *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
   *             @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
   *             @OA\Property(property="token_type", type="string", example="Bearer"),
   *             @OA\Property(property="expires_in", type="integer", example=900, description="Access token TTL in seconds"),
   *             @OA\Property(property="user", ref="#/components/schemas/User")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Validation error (e.g., missing or invalid email/password)",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="messages", type="object")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Invalid credentials",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Invalid credentials")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   *
   * @return ResponseInterface
   */
  public function login(): ResponseInterface
  {
    try {
      $rules = [
        'email' => 'required|valid_email',
        'password' => 'required'
      ];

      $data = $this->request->getJSON(true);

      if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
      }

      $result = $this->authService->attempt($data['email'], $data['password']);

      if (!$result) {
        return $this->failUnauthorized('Invalid credentials');
      }

      return $this->respond($result);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }
  }

  /**
   * @OA\Post(
   *     path="/auth/forgot-password",
   *     tags={"Auth"},
   *     summary="Request a password reset email",
   *     operationId="forgotPassword",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"email"},
   *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="If email is valid, a password reset link will be sent (or simulated). Always returns success to prevent user enumeration.",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="If your email address is in our system, you will receive a password reset link shortly.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Invalid input (e.g., email not provided or invalid format)",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="messages", type="object")
   *         )
   *     ),
   *    @OA\Response(
   *         response=500,
   *         description="Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Internal Server Error"
   *             )
   *         )
   *     )
   * )
   * @return ResponseInterface
   */
  public function forgotPassword(): ResponseInterface
  {
    try {
      $rules = [
        'email' => 'required|valid_email',
      ];

      $data = $this->request->getJSON(true);

      if (!$this->validateData($data, $rules)) { // Use validateData for JSON
        return $this->failValidationErrors($this->validator->getErrors());
      }

      $this->authService->handleForgotPasswordRequest($data['email']);

      // Always return a generic success message to prevent user enumeration
      return $this->respond([
        'message' => 'If your email address is in our system, you will receive a password reset link shortly.'
      ]);

    } catch (Exception $e) {
      log_message('error', '[Auth] Forgot Password Error: ' . $e->getMessage());
      return $this->failServerError('An unexpected error occurred. Please try again later.');
    }
  }

  /**
   * @OA\Post(
   *     path="/auth/verify-reset-token",
   *     tags={"Auth"},
   *     summary="Verifies a password reset token",
   *     operationId="verifyPasswordResetToken",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"token"},
   *             @OA\Property(property="token", type="string", example="your.jwt.token.here")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Token is valid",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Token is valid.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Invalid input (e.g., token not provided)",
   *          @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="messages", type="object")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Invalid or expired token",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Invalid or expired password reset token.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   * @return ResponseInterface
   */
  public function verifyResetToken(): ResponseInterface
  {
    try {
      $rules = ['token' => 'required|string'];
      $data = $this->request->getJSON(true);

      if (!$this->validateData($data, $rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
      }

      $token = $data['token'];
      // Pass true to throw exceptions for detailed error handling
      $this->authService->validatePasswordResetToken($token, true);

      return $this->respond(['message' => 'Token is valid.']);
    } catch (Exception $e) {
      log_message('error', '[Auth] Verify Reset Token Error: ' . $e->getMessage());
      return $this->failServerError('An unexpected error occurred while verifying the token.');
    }
  }


  /**
   * @OA\Post(
   *     path="/auth/reset-password",
   *     tags={"Auth"},
   *     summary="Resets user password using a token",
   *     operationId="resetUserPassword",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"token", "password", "password_confirm"},
   *             @OA\Property(property="token", type="string", example="your.jwt.reset.token"),
   *             @OA\Property(property="password", type="string", format="password", example="newSecurePassword123"),
   *             @OA\Property(property="password_confirm", type="string", format="password", example="newSecurePassword123")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Password reset successful",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="Password has been updated successfully.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Validation error (e.g., passwords don't match, token missing)",
   *         @OA\JsonContent(type="object", @OA\Property(property="messages", type="object"))
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Invalid, expired, or misused token",
   *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="User not found for the token",
   *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Server Error or password update failure",
   *         @OA\JsonContent(type="object",@OA\Property(property="message", type="string"))
   *     )
   * )
   * @return ResponseInterface
   */
  public function resetPassword(): ResponseInterface
  {
    $rules = [
      'token' => 'required|string',
      'password' => 'required|min_length[8]|matches[password_confirm]',
      'password_confirm' => 'required',
    ];

    $data = $this->request->getJSON(true);

    if (!$this->validateData($data, $rules)) {
      return $this->failValidationErrors($this->validator->getErrors());
    }

    try {
      $success = $this->authService->resetPassword($data['token'], $data['password']);

      if ($success) {
        return $this->respond(['message' => 'Password has been updated successfully.']);
      } else {
        // This case implies a failure within resetPassword after token validation (e.g., DB error)
        // or if validatePasswordResetToken didn't throw an exception but returned null (if throwExceptions=false)
        return $this->failServerError('Could not update password at this time. Please try again.');
      }
    } catch (InvalidArgumentException $e) { // Token not provided (should be caught by initial validation)
      return $this->failValidationErrors(['token' => $e->getMessage()]);
    } catch (ExpiredException $e) {
      return $this->failUnauthorized('Password reset token has expired.');
    } catch (SignatureInvalidException $e) {
      return $this->failUnauthorized('Invalid password reset token signature.');
    } catch (BeforeValidException $e) {
      return $this->failUnauthorized('Password reset token not yet valid.');
    } catch (UnexpectedValueException $e) { // Invalid purpose or missing UID
      return $this->failUnauthorized('Invalid password reset token content.');
    } catch (RuntimeException $e) { // User not found for token, or DB update failed in UserService
      // Distinguish based on message if needed, or keep generic
      log_message('error', '[Auth] Reset Password Runtime Error: ' . $e->getMessage());
      return $this->fail($e->getMessage() ?? 'Could not update password due to a user or system error.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    } catch (Exception $e) {
      log_message('error', '[Auth] Reset Password Error: ' . $e->getMessage());
      return $this->failServerError('An unexpected error occurred. Please try again later.');
    }
  }

  public function logout(): ResponseInterface
  {
    return $this->respond(['message' => 'Logged out successfully.']);
  }

  /**
   * @OA\Post(
   *     path="/auth/refresh-token",
   *     tags={"Auth"},
   *     summary="Refresh JWT tokens",
   *     security={{"bearerAuth":{}}},
   *     description="Exchanges a valid refresh token for a new access token and a new refresh token.",
   *     operationId="refreshTokens",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"refresh_token"},
   *             @OA\Property(
   *                 property="refresh_token",
   *                 type="string",
   *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="New tokens issued successfully",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
   *             @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
   *             @OA\Property(property="token_type", type="string", example="Bearer"),
   *             @OA\Property(property="expires_in", type="integer", example=900, description="Access token TTL in seconds")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Validation error (e.g., missing refresh_token)",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="messages", type="object")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="integer", example=401),
   *             @OA\Property(property="error", type="integer", example=401),
   *             @OA\Property(
   *                 property="messages",
   *                 type="object",
   *                 @OA\Property(property="error", type="string", example="Unauthorized")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="message", type="string", example="An unexpected error occurred while refreshing the token.")
   *         )
   *     )
   * )
   */
  public function refreshToken(): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true) ?? [];
      if (!isset($data['refresh_token']) || !is_string($data['refresh_token'])) {
        return $this->failValidationErrors(['refresh_token' => 'Refresh token is required.']);
      }

      // Valida refresh token (purpose=refresh)
      $user = $this->authService->validateToken($data['refresh_token'], 'refresh');
      if (!$user) {
        return $this->failUnauthorized('Invalid or expired refresh token.');
      }

      // Emite novo par de tokens (rotaciona refresh)
      $tokens = $this->authService->issueTokens($user);

      return $this->respond([
        'access_token' => $tokens['access_token'],
        'refresh_token' => $tokens['refresh_token'],
        'token_type' => 'Bearer',
        'expires_in' => config('Jwt')->accessTtl,
      ]);
    } catch (ExpiredException $e) {
      return $this->failUnauthorized('Refresh token has expired.');
    } catch (SignatureInvalidException $e) {
      return $this->failUnauthorized('Invalid refresh token signature.');
    } catch (BeforeValidException $e) {
      return $this->failUnauthorized('Refresh token not yet valid.');
    } catch (Exception $e) {
      return $this->failServerError('An unexpected error occurred while refreshing the token.');
    }
  }

  /**
   * @OA\Post(
   *     path="/auth/register",
   *     tags={"Auth"},
   *     summary="Create a new user",
   *     security={{"bearerAuth":{}}},
   *     operationId="register",
   *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserInput")),
   *     @OA\Response(response=201, description="User created successfully", @OA\JsonContent(ref="#/components/schemas/User")),
   *     @OA\Response(
   *         response=422,
   *         description="Validation Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="errors", type="object", @OA\Property(property="email", type="string", example="The email field is required."), @OA\Property(property="name", type="string", example="The name field is required."), @OA\Property(property="password", type="string", example="The password field is required."))
   *         )
   *     ),
   *     @OA\Response(response=500, description="Server Error", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Internal Server Error")))
   * )
   */
  public function register(): ResponseInterface
  {
    try {
      $data = $this->request->getJSON(true);
      $id = $this->userService->create($data);
      if (!$id) {
        return $this->failValidationErrors($this->userService->getErrors());
      }
      $data = $this->userService->getById($id);
      return (!$data) ? $this->failNotFound() : $this->respond($data, 201);
    } catch (Exception $e) {
      return $this->failServerError($e->getMessage());
    }

  }
}