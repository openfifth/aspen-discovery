<?php

require_once ROOT_DIR . '/sys/OAuth2/OAuth2ServerConfig.php';

/**
 * OAuth2 Authentication middleware for API endpoints
 */
class OAuth2Middleware {
	
	private static $resourceServer = null;
	private static $authenticatedUser = null;
	private static $currentScopes = [];
	
	/**
	 * Authenticate an API request using OAuth2 Bearer token
	 * @param array $requiredScopes Optional array of required scopes
	 * @return bool True if authentication successful, false otherwise
	 */
	public static function authenticate(array $requiredScopes = []): bool {
		try {
			if (self::$resourceServer === null) {
				OAuth2ServerConfig::generateKeyPairIfNeeded();
				self::$resourceServer = OAuth2ServerConfig::getResourceServer();
			}

			// Create PSR-7 request from current request
			$request = self::createPsr7Request();
			
			// Validate the access token
			$request = self::$resourceServer->validateAuthenticatedRequest($request);
			
			// Get user ID and scopes from the validated token
			$userId = $request->getAttribute('oauth_user_id');
			$tokenScopes = $request->getAttribute('oauth_scopes', []);
			
			self::$currentScopes = is_array($tokenScopes) ? $tokenScopes : explode(' ', $tokenScopes);
			
			// Load the authenticated user
			if ($userId) {
				self::$authenticatedUser = new User();
				self::$authenticatedUser->id = $userId;
				if (!self::$authenticatedUser->find(true)) {
					return false;
				}
			}
			
			// Check required scopes
			if (!empty($requiredScopes)) {
				foreach ($requiredScopes as $requiredScope) {
					if (!in_array($requiredScope, self::$currentScopes)) {
						return false;
					}
				}
			}
			
			return true;
			
		} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
			self::sendOAuthErrorResponse($exception);
			return false;
		} catch (\Exception $exception) {
			self::sendGenericErrorResponse();
			return false;
		}
	}
	
	/**
	 * Get the authenticated user from OAuth2 token
	 * @return User|null
	 */
	public static function getAuthenticatedUser(): ?User {
		return self::$authenticatedUser;
	}
	
	/**
	 * Get the scopes granted to the current token
	 * @return array
	 */
	public static function getCurrentScopes(): array {
		return self::$currentScopes;
	}
	
	/**
	 * Check if current token has a specific scope
	 * @param string $scope
	 * @return bool
	 */
	public static function hasScope(string $scope): bool {
		return in_array($scope, self::$currentScopes);
	}
	
	/**
	 * Require authentication and specific scopes, exit with error if not met
	 * @param array $requiredScopes
	 */
	public static function requireAuth(array $requiredScopes = []): void {
		if (!self::authenticate($requiredScopes)) {
			exit; // Error response already sent
		}
	}
	
	private static function createPsr7Request() {
		return new class {
			private $headers;
			private $attributes = [];
			
			public function __construct() {
				$this->headers = getallheaders() ?: [];
			}
			
			public function getHeader($name) {
				$name = strtolower($name);
				foreach ($this->headers as $headerName => $headerValue) {
					if (strtolower($headerName) === $name) {
						return is_array($headerValue) ? $headerValue : [$headerValue];
					}
				}
				return [];
			}
			
			public function getHeaderLine($name) {
				$header = $this->getHeader($name);
				return implode(',', $header);
			}
			
			public function getAttribute($name, $default = null) {
				return $this->attributes[$name] ?? $default;
			}
			
			public function withAttribute($name, $value) {
				$this->attributes[$name] = $value;
				return $this;
			}
			
			public function getServerParams() {
				return $_SERVER;
			}
		};
	}
	
	private static function sendOAuthErrorResponse(\League\OAuth2\Server\Exception\OAuthServerException $exception): void {
		http_response_code($exception->getHttpStatusCode());
		header('Content-Type: application/json');
		echo json_encode([
			'error' => $exception->getErrorType(),
			'error_description' => $exception->getMessage(),
		]);
	}
	
	private static function sendGenericErrorResponse(): void {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode([
			'error' => 'unauthorized',
			'error_description' => 'Invalid or missing access token',
		]);
	}
}
