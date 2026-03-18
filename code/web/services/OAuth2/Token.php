<?php

require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2ServerConfig.php';

class OAuth2_Token extends JSON_Action {
	
	function launch() {
		// Generate keys if they don't exist
		OAuth2ServerConfig::generateKeyPairIfNeeded();

		$server = OAuth2ServerConfig::getAuthorizationServer();

		try {
			// Create PSR-7 request from PHP globals
			$request = $this->createPsr7Request();
			
			// Create PSR-7 response
			$response = $this->createPsr7Response();

			// Handle the token request
			$response = $server->respondToAccessTokenRequest($request, $response);

			// Send the response
			$this->sendPsr7Response($response);

		} catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {
			// Handle OAuth2 specific exceptions
			http_response_code($exception->getHttpStatusCode());
			header('Content-Type: application/json');
			echo json_encode([
				'error' => $exception->getErrorType(),
				'error_description' => $exception->getMessage(),
			]);

		} catch (\Exception $exception) {
			// Handle general exceptions
			http_response_code(500);
			header('Content-Type: application/json');
			echo json_encode([
				'error' => 'server_error',
				'error_description' => 'Internal server error',
			]);
		}
	}

	private function createPsr7Request() {
		// Create a simple PSR-7 compatible request object
		return new class {
			private $headers;
			private $body;
			private $method;
			private $parsedBody;

			public function __construct() {
				$this->method = $_SERVER['REQUEST_METHOD'];
				$this->headers = getallheaders() ?: [];
				$this->body = file_get_contents('php://input');
				
				if ($this->method === 'POST') {
					if (isset($_POST) && !empty($_POST)) {
						$this->parsedBody = $_POST;
					} else {
						// Try to parse JSON body
						$json = json_decode($this->body, true);
						if ($json !== null) {
							$this->parsedBody = $json;
						} else {
							// Try to parse form data
							parse_str($this->body, $this->parsedBody);
						}
					}
				}
			}

			public function getMethod() {
				return $this->method;
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

			public function getParsedBody() {
				return $this->parsedBody;
			}

			public function getServerParams() {
				return $_SERVER;
			}

			public function getAttribute($name, $default = null) {
				return $default;
			}

			public function withAttribute($name, $value) {
				return $this;
			}
		};
	}

	private function createPsr7Response() {
		// Create a simple PSR-7 compatible response object
		return new class {
			private $statusCode = 200;
			private $headers = [];
			private $body = '';

			public function withStatus($code, $reasonPhrase = '') {
				$this->statusCode = $code;
				return $this;
			}

			public function withHeader($name, $value) {
				$this->headers[$name] = $value;
				return $this;
			}

			public function withBody($body) {
				$this->body = $body;
				return $this;
			}

			public function getStatusCode() {
				return $this->statusCode;
			}

			public function getHeaders() {
				return $this->headers;
			}

			public function getBody() {
				return $this->body;
			}

			public function withJson(array $data) {
				$this->headers['Content-Type'] = 'application/json';
				$this->body = json_encode($data);
				return $this;
			}
		};
	}

	private function sendPsr7Response($response) {
		// Set HTTP status code
		http_response_code($response->getStatusCode());

		// Set headers
		foreach ($response->getHeaders() as $name => $value) {
			header($name . ': ' . (is_array($value) ? implode(', ', $value) : $value));
		}

		// Output body
		echo $response->getBody();
	}
}
