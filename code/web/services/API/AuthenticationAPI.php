<?php

require_once ROOT_DIR . '/services/API/AbstractAPI.php';
require_once ROOT_DIR . '/CatalogConnection.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Middleware.php';

class AuthenticationAPI extends AbstractAPI {
	function launch(): void {
		$method = (isset($_GET['method']) && !is_array($_GET['method'])) ? $_GET['method'] : '';
		$output = '';

		header('Content-type: application/json');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past

		global $activeLanguage;
		if (isset($_GET['language'])) {
			$language = new Language();
			$language->code = $_GET['language'];
			if ($language->find(true)) {
				$activeLanguage = $language;
			}
		}

		$oauthAuthenticated = false;
		$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
		if (str_starts_with($authHeader, 'Bearer ')) {
			if (OAuth2Middleware::authenticate()) {
				$oauthAuthenticated = true;
				global $oauthUser;
				$oauthUser = OAuth2Middleware::getAuthenticatedUser();
			}
		}

		if ($oauthAuthenticated) {
			if ($method == 'isLoggedIn') {
				header("Cache-Control: max-age=10800");
				require_once ROOT_DIR . '/sys/SystemLogging/APIUsage.php';
				APIUsage::incrementStat('AuthenticationAPI', $method);
				$output = json_encode(['result' => $this->$method()]);
			} else {
				header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
				$output = json_encode(['error' => 'invalid_method']);
			}

			ExternalRequestLogEntry::logRequest('AuthenticationAPI.' . $method, $_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], getallheaders(), '', $_SERVER['REDIRECT_STATUS'], $output, []);
			echo $output;
		} else {
			// needs to be in front of OAuth because we don't yet have authorization which comes after exchangePasswordForToken
			header('Cache-Control: no-cache, must-revalidate');
			if ($method == 'exchangePasswordForToken') {
				require_once ROOT_DIR . '/sys/SystemLogging/APIUsage.php';
				APIUsage::incrementStat('AuthenticationAPI', $method);
				$output = json_encode(['result' => $this->$method()]);
			} else {
				// HTTP/1.1
				header('HTTP/1.0 401 Unauthorized');
				$output = json_encode([
					'error' => 'oauth_authentication_required',
					'error_description' => 'OAuth2 Bearer token required for this method'
				]);
			}
			echo $output;
		}
	}

	function getBreadcrumbs(): array {
		return [];
	}

	/**
	 * Exchange user credentials for OAuth2 tokens using password grant
	 * This method provides OAuth2 token authentication for trusted applications like LiDA
	 **
	 * Required Parameters:
	 * - username: Library card number or username
	 * - password: Password/PIN
	 * - client_id: OAuth2 client identifier
	 * - client_secret: OAuth2 client secret
	 * - scope: Optional space-separated list of requested scopes
	 *
	 * @return array OAuth2 token response or error
	 */
	function exchangePasswordForToken(): array {
		// Get request data from POST body or form data
		$input = json_decode(file_get_contents('php://input'), true);
		if (empty($input)) {
			$input = $_POST;
		}

		// Validate required parameters
		$username = $input['username'] ?? '';
		$password = $input['password'] ?? '';
		$clientId = $input['client_id'] ?? '';
		$clientSecret = $input['client_secret'] ?? '';
		$scope = $input['scope'] ?? '';

		if (empty($username) || empty($password) || empty($clientId) || empty($clientSecret)) {
			return [
				'success' => false,
				'error' => 'invalid_request',
				'error_description' => 'Missing required parameters: username, password, client_id, client_secret'
			];
		}

		try {
			// Validate client credentials first
			require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Client.php';

			$client = new OAuth2Client();
			$client->client_id = $clientId;
			if (!$client->find(true)) {
				return [
					'success' => false,
					'error' => 'invalid_client',
					'error_description' => 'Client authentication failed'
				];
			}

			// Verify client is active
			if (!$client->is_active) {
				return [
					'success' => false,
					'error' => 'invalid_client',
					'error_description' => 'Client has been disabled'
				];
			}

			// Verify client secret
			if (!password_verify($clientSecret, $client->client_secret)) {
				return [
					'success' => false,
					'error' => 'invalid_client',
					'error_description' => 'Client authentication failed'
				];
			}

			// Verify client supports password grant (check client type)
			if (isset($client->client_type) && $client->client_type !== 'native_application') {
				return [
					'success' => false,
					'error' => 'unauthorized_client',
					'error_description' => 'Client not authorized for password grant'
				];
			}

			// Apply rate limiting for token requests
			require_once ROOT_DIR . '/sys/Authentication/OAuth2/RateLimiter/OAuth2RateLimiter.php';

			if (!OAuth2RateLimiter::enforce('token', $clientId)) {
				return [
					'success' => false,
					'error' => 'rate_limit_exceeded',
					'error_description' => 'Too many token requests. Please try again later.'
				];
			}

			try {
				$validUser = UserAccount::validateAccount($username, $password);
				if (!$validUser || ($validUser instanceof AspenError)) {
					return [
						'success' => false,
						'error' => 'invalid_grant',
						'error_description' => 'Invalid username or password'
					];
				}
			} catch (\Exception $userValidationError) {
				return [
					'success' => false,
					'error' => 'invalid_grant',
					'error_description' => 'Invalid username or password'
				];
			}

			// Use internal curl call to OAuth2 Token endpoint to avoid PSR-7 interface issues
			$tokenEndpointUrl = $this->getBaseUrl() . '/OAuth2/Token';

			$postData = [
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password,
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'scope' => $scope
			];

			$curlHandle = curl_init();
			curl_setopt($curlHandle, CURLOPT_URL, $tokenEndpointUrl);
			curl_setopt($curlHandle, CURLOPT_POST, true);
			curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($postData));
			curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
			curl_setopt($curlHandle, CURLOPT_HTTPHEADER, [
				'Content-Type: application/x-www-form-urlencoded',
				'User-Agent: Aspen-AuthenticationAPI/1.0'
			]);

			$response = curl_exec($curlHandle);
			$httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
			$curlError = curl_error($curlHandle);
			curl_close($curlHandle);

			if ($curlError) {
				return [
					'success' => false,
					'error' => 'server_error',
					'error_description' => 'Failed to connect to OAuth2 token endpoint: ' . $curlError
				];
			}

			$tokenData = json_decode($response, true);

			if ($httpCode === 200 && $tokenData && isset($tokenData['access_token'])) {
				return [
					'success' => true,
					'access_token' => $tokenData['access_token'],
					'token_type' => $tokenData['token_type'] ?? 'Bearer',
					'expires_in' => $tokenData['expires_in'] ?? 3600,
					'refresh_token' => $tokenData['refresh_token'] ?? null,
					'scope' => $tokenData['scope'] ?? $scope
				];
			} else {
				$error = $tokenData['error'] ?? 'token_generation_failed';
				$errorDescription = $tokenData['error_description'] ?? 'Failed to generate OAuth2 token';

				return [
					'success' => false,
					'error' => $error,
					'error_description' => $errorDescription
				];
			}

		} catch (Exception $e) {
			return [
				'success' => false,
				'error' => 'server_error',
				'error_description' => 'Internal server error during token generation: ' . $e->getMessage()
			];
		}
	}

	/**
	 * Get the base URL for internal API calls
	 */
	private function getBaseUrl(): string {
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'];
		$path = dirname($_SERVER['SCRIPT_NAME']);
		if ($path === '/') {
			$path = '';
		}
		return $protocol . '://' . $host . $path;
	}

	/**
	 * Check if user is logged in (OAuth2 compatible method)
	 */
	function isLoggedIn(): bool {
		// If OAuth2 authenticated, check if user exists
		global $oauthUser;
		if (isset($oauthUser) && $oauthUser) {
			return true;
		}

		return false;
	}
}
