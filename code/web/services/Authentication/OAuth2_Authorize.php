<?php

use League\OAuth2\Server\Exception\OAuthServerException;

require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2ServerConfig.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Client.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/RateLimiter/OAuth2RateLimiter.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2UserEntity.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/PSR7/SimpleServerRequest.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/PSR7/SimpleResponse.php';


class Authentication_OAuth2_Authorize extends JSON_Action {
	/**
	 * @param null $method
	 * @throws Exception
	 */
	function launch($method = null): void {
		if (!OAuth2RateLimiter::enforce('auth')) {
			return; // Rate limit response already sent
		}

		OAuth2ServerConfig::generateKeyPairIfNeeded();
		$server = OAuth2ServerConfig::getAuthorizationServer();

		try {
			$request = $this->createServerRequest();
			$authRequest = $server->validateAuthorizationRequest($request);

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				if (isset($_POST['username']) && isset($_POST['password'])) {
					$loginResult = $this->handleLogin($_POST['username'], $_POST['password']);
					if (!$loginResult) {
						http_response_code(401);
						header('Content-Type: application/json');
						echo json_encode([
							'error' => 'invalid_credentials',
							'error_description' => 'Invalid username or password.'
						]);
						return;
					}

				}

				if (!UserAccount::isLoggedIn()) {
					http_response_code(401);
					header('Content-Type: application/json');
					echo json_encode([
						'error' => 'authentication_required',
						'error_description' => 'User must be authenticated to approve authorization.',
						'instructions' => 'POST with username and password to authenticate first'
					]);
					return;
				}

				$approved = isset($_POST['approve']) && $_POST['approve'] === 'yes';

				if ($approved) {
					global $user;
					$userEntity = new OAuth2UserEntity();
					$userEntity->setIdentifier($user->id);
					$authRequest->setUser($userEntity);
					$authRequest->setAuthorizationApproved(true);
				} else {
					$authRequest->setAuthorizationApproved(false);
				}

				$response = $this->createResponse();
				$response = $server->completeAuthorizationRequest($authRequest, $response);
				$this->sendPsr7Response($response);
				return;
			}

			$this->handleApiAuthorizationRequest($authRequest);

		} catch (OAuthServerException $exception) {
			http_response_code($exception->getHttpStatusCode());
			header('Content-Type: application/json');
			echo json_encode([
				'error' => $exception->getErrorType(),
				'error_description' => $exception->getMessage(),
			]);

		} catch (Exception $exception) {
			http_response_code(500);
			header('Content-Type: application/json');
			echo json_encode([
				'error' => 'server_error',
				'error_description' => 'Internal server error: ' . $exception->getMessage(),
			]);
		}
	}

	private function createServerRequest(): SimpleServerRequest {
		return new SimpleServerRequest();
	}

	private function createResponse() {
		return new SimpleResponse();
	}

	private function sendPsr7Response($response): void {
		http_response_code($response->getStatusCode());
		foreach ($response->getHeaders() as $name => $values) {
			foreach ((array)$values as $value) {
				header($name . ': ' . $value, false);
			}
		}
		echo $response->getBody();
	}

	/**
	 * Handle authorization requests
	 */
	private function handleApiAuthorizationRequest($authRequest): void {
		$clientId = $authRequest->getClient()->getIdentifier();
		$client = new OAuth2Client();
		$client->setClientId($clientId);
		$client->find(true);

		$scopes = [];
		$scopeDescriptions = [];
		foreach ($authRequest->getScopes() as $scope) {
			$scopeId = $scope->getIdentifier();
			$scopes[] = $scopeId;
			$scopeDescriptions[$scopeId] = $this->getScopeDescription($scopeId);
		}

		$userInfo = null;
		if (UserAccount::isLoggedIn()) {
			global $user;
			$userInfo = [
				'id' => $user->id,
				'name' => $user->displayName ?? ($user->firstname . ' ' . $user->lastname),
				'username' => $user->cat_username ?? $user->ils_barcode,
				'status' => 'authenticated'
			];
		}

		header('Content-Type: application/json');
		echo json_encode([
			'client' => [
				'id' => $client->getClientId(),
				'name' => $client->getName(),
			],
			'scopes' => $scopeDescriptions,
			'user' => $userInfo
		]);
	}


	/**
	 * Get human-readable description for scope
	 */
	private function getScopeDescription(string $scope): string {
		$descriptions = OAuth2Client::getScopeOptions();

		return $descriptions[$scope] ?? $scope;
	}

	/**
	 * Handle login attempt
	 */
	private function handleLogin(string $username, string $password): bool {
		require_once ROOT_DIR . '/sys/Account/UserAccount.php';

		$user = UserAccount::validateAccount($username, $password);
		if ($user && !($user instanceof AspenError)) {
			UserAccount::login($user);
			return true;
		}
		return false;
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
