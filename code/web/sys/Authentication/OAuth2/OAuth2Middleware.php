<?php

use League\OAuth2\Server\Exception\OAuthServerException;

require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2ServerConfig.php';
require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';

class OAuth2Middleware {

	private static $resourceServer = null;
	private static ?User $authenticatedUser = null;
	private static ?string $authenticatedClientId = null;
	private static array $currentScopes = [];

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

			$request = self::createPsr7Request();
			$request = self::$resourceServer->validateAuthenticatedRequest($request);
			$userId = $request->getAttribute('oauth_user_id');
			$clientId = $request->getAttribute('oauth_client_id');
			$tokenScopes = $request->getAttribute('oauth_scopes', []);
			self::$currentScopes = is_array($tokenScopes) ? $tokenScopes : explode(' ', $tokenScopes);
			
			self::$authenticatedUser = null;
			self::$authenticatedClientId = $clientId;

			if ($userId) {
				self::$authenticatedUser = new User();
				self::$authenticatedUser->id = $userId;
				if (!self::$authenticatedUser->find(true)) {
					return false;
				}
			} else {
				self::$authenticatedUser = null;
			}

			if (!empty($requiredScopes)) {
				foreach ($requiredScopes as $requiredScope) {
					if (!in_array($requiredScope, self::$currentScopes)) {
						return false;
					}
				}
			}

			return true;

		} catch (OAuthServerException $exception) {
			self::sendOAuthErrorResponse($exception);
			return false;
		} catch (Exception $exception) {
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
	 * Get the authenticated client ID from OAuth2 token
	 * @return string|null
	 */
	public static function getAuthenticatedClientId(): ?string {
		return self::$authenticatedClientId;
	}

	private static function createPsr7Request() {
		require_once ROOT_DIR . '/sys/Authentication/OAuth2/PSR7/SimpleServerRequest.php';
		return new SimpleServerRequest();
	}

	private static function sendOAuthErrorResponse(OAuthServerException $exception): void {
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
