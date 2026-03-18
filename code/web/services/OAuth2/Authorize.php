<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2ServerConfig.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2Client.php';

class OAuth2_Authorize extends Action {

	function launch() {
		// Generate keys if they don't exist
		OAuth2ServerConfig::generateKeyPairIfNeeded();

		$server = OAuth2ServerConfig::getAuthorizationServer();

		try {
			// Create PSR-7 request from PHP globals
			$request = $this->createPsr7Request();

			// Validate the authorization request
			$authRequest = $server->validateAuthorizationRequest($request);

			// Check if user is logged in
			if (!UserAccount::isLoggedIn()) {
				// Redirect to login with return URL
				$returnUrl = $_SERVER['REQUEST_URI'];
				header('Location: /MyAccount/Login?returnUrl=' . urlencode($returnUrl));
				exit;
			}

			// Check if this is a POST request (user submitted the authorization form)
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				// User has submitted the form
				$approved = isset($_POST['approve']) && $_POST['approve'] === 'yes';

				if ($approved) {
					global $user;
					// User approved the request
					$userEntity = new \League\OAuth2\Server\Entities\UserEntity();
					$userEntity->setIdentifier($user->id);
					$authRequest->setUser($userEntity);
					$authRequest->setAuthorizationApproved(true);
				} else {
					// User denied the request
					$authRequest->setAuthorizationApproved(false);
				}

				// Return the authorization response
				$response = $this->createPsr7Response();
				$response = $server->completeAuthorizationRequest($authRequest, $response);
				$this->sendPsr7Response($response);
				return;
			}

			// Show authorization form to user
			$this->showAuthorizationForm($authRequest);

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
				'error_description' => 'Internal server error: ' . $exception->getMessage(),
			]);
		}
	}

	private function showAuthorizationForm($authRequest) {
		global $interface;
		global $user;

		// Get client information
		$clientId = $authRequest->getClient()->getIdentifier();
		$client = new OAuth2Client();
		$client->client_id = $clientId;
		$client->find(true);

		// Get requested scopes
		$scopes = [];
		foreach ($authRequest->getScopes() as $scope) {
			$scopes[] = $scope->getIdentifier();
		}

		$interface->assign('client', $client);
		$interface->assign('scopes', $scopes);
		$interface->assign('user', $user);
		$interface->assign('authorizationUrl', $_SERVER['REQUEST_URI']);

		$this->display('oauth2_authorize.tpl', 'Authorize Application', '');
	}

	private function createPsr7Request() {
		// Create a simple PSR-7 compatible request object
		return new class {
			private $headers;
			private $body;
			private $method;
			private $query;

			public function __construct() {
				$this->method = $_SERVER['REQUEST_METHOD'];
				$this->headers = getallheaders() ?: [];
				$this->body = file_get_contents('php://input');
				$this->query = $_GET;
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

			public function getQueryParams() {
				return $this->query;
			}

			public function getParsedBody() {
				return $_POST;
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

			public function getUri() {
				return new class {
					public function getQuery() {
						return $_SERVER['QUERY_STRING'] ?? '';
					}
				};
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

			public function withAddedHeader($name, $value) {
				if (isset($this->headers[$name])) {
					if (!is_array($this->headers[$name])) {
						$this->headers[$name] = [$this->headers[$name]];
					}
					$this->headers[$name][] = $value;
				} else {
					$this->headers[$name] = $value;
				}
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
		};
	}

	private function sendPsr7Response($response) {
		// Set HTTP status code
		http_response_code($response->getStatusCode());

		// Set headers
		foreach ($response->getHeaders() as $name => $value) {
			if (is_array($value)) {
				foreach ($value as $v) {
					header($name . ': ' . $v, false);
				}
			} else {
				header($name . ': ' . $value);
			}
		}

		// Output body
		echo $response->getBody();
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Authorize Application');
		return $breadcrumbs;
	}
}
