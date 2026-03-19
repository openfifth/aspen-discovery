<?php

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;

require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2ServerConfig.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/RateLimiter/OAuth2RateLimiter.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/PSR7/SimpleServerRequest.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/PSR7/SimpleResponse.php';

class Authentication_OAuth2_Token extends JSON_Action {

	/**
	 * @throws Exception
	 */
	function launch($method = null): void {
		if (!OAuth2RateLimiter::enforce('token')) {
			return;
		}

		OAuth2ServerConfig::generateKeyPairIfNeeded();

		$server = OAuth2ServerConfig::getAuthorizationServer();

		try {
			$request = new SimpleServerRequest();
			$response = new SimpleResponse();
			$response = $server->respondToAccessTokenRequest($request, $response);

			$requestedScopes = $this->getRequestedScopes();

			// check for openid request
			if (in_array('openid', $requestedScopes)) {
				$response = $this->addIDTokenToResponse($response);
			}

			$this->sendPsr7Response($response);

		} catch (OAuthServerException $exception) {
			http_response_code($exception->getHttpStatusCode());
			header('Content-Type: application/json');
			
			echo json_encode(array_merge([
				'error' => $exception->getErrorType(),
				'error_description' => $exception->getMessage(),
			]));

		} catch (Exception $exception) {
			http_response_code(500);
			header('Content-Type: application/json');
			echo json_encode([
				'error' => 'server_error',
				'error_description' => $exception->getMessage(),
			]);
		}
	}

	private function sendPsr7Response($response): void {
		http_response_code($response->getStatusCode());

		foreach ($response->getHeaders() as $name => $values) {
			if (is_array($values)) {
				foreach ($values as $value) {
					header($name . ': ' . $value, false);
				}
			} else {
				header($name . ': ' . $values);
			}
		}

		$body = $response->getBody();
		if (method_exists($body, '__toString')) {
			echo $body->__toString();
		} else {
			echo $body;
		}
	}

	private function getRequestedScopes(): array {
		$scopes = $_POST['scope'] ?? '';
		$scopeArray = array_filter(explode(' ', $scopes));

		if (count($scopeArray) <= 1 && strpos($scopes, ',') !== false) {
			$scopeArray = array_filter(explode(',', $scopes));
		}

		return array_map('trim', $scopeArray);
	}

	private function getUserFromRequest(): ?array {
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';

		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = UserAccount::validateAccount($username, $password);

		if ($user) {
			return [
				'id' => $user->id,
				'username' => $user->username,
				'email' => $user->email ?? '',
				'firstName' => '',
				'lastName' => '',
				'homeLibrary' => $user->homeLibrary ?? null,
				'source' => $user->source ?? 'ils',
			];
		}

		return null;
	}

	private function getIssuer(): string {
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'];
		return $protocol . '://' . $host;
	}

	/**
	 *  Add ID Token to the OAuth2 response for OpenID Connect
	 */
	private function addIDTokenToResponse(ResponseInterface $response) {
		try {
			$body = json_decode($response->getBody()->__toString(), true);

			if (!is_array($body)) {
				return $response;
			}

			$clientId = $_POST['client_id'] ?? '';
			$user = $this->getUserFromRequest();

			if ($user) {
				$issuer = $this->getIssuer();
				$idToken = OAuth2ServerConfig::generateIDToken($user, $clientId, $issuer);
				$body['id_token'] = $idToken;
			}

			$newResponse = new SimpleResponse();
			$newResponse->getBody()->write(json_encode($body));

			$newResponse = $newResponse->withStatus($response->getStatusCode());
			foreach ($response->getHeaders() as $name => $values) {
				foreach ($values as $value) {
					$newResponse = $newResponse->withAddedHeader($name, $value);
				}
			}

			return $newResponse->withHeader('Content-Type', 'application/json');

		} catch (Exception $e) {
			// If ID token generation fails, return original response
			return $response;
		}
	}
}
