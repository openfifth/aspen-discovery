<?php

use League\OAuth2\Server\Exception\OAuthServerException;

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
			
			$debugDetails = [
				'exception_type' => get_class($exception),
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'timestamp' => date('Y-m-d H:i:s'),
			];
			file_put_contents('/tmp/oauth2_exception_debug.log', json_encode($debugDetails) . "\n", FILE_APPEND);
			
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
}
