<?php

abstract class AbstractAPI extends Action{
	protected $context;
	protected string $apiName = '';
	protected $authorizedUser = false;
	protected string $authorizedScope = '';
	
	function __construct($context = 'external') {
		parent::__construct(false);
		$this->context = $context;
		if ($this->checkIfLiDA()) {
			$this->context = 'lida';
		}
		if (empty($this->apiName)) {
			$this->apiName = (new ReflectionClass($this))->getShortName();
		}
	}

	function checkIfLiDA(): bool {
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if ($name == 'User-Agent' || $name == 'user-agent') {
					if (str_contains($value, "Aspen LiDA")) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function getLiDAVersion() {
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if ($name == 'version' || $name == 'Version') {
					$version = explode(' ', $value);
					$version = substr($version[0], 1); // remove starting 'v'
					return floatval($version);
				}
			}
		}
		return 0;
	}

	function getLiDASession() {
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if ($name == 'LiDA-SessionID' || $name == 'lida-sessionid') {
					$sessionId = explode(' ', $value);
					return $sessionId[0];
				}
			}
		}
		return false;
	}

	function getLiDASlug() {
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if (strcasecmp($name, 'lida-slug') === 0) {
					return $value;
				}
			}
		}
		return false;
	}

	function getLiDAUserAgent() {
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if ($name == 'User-Agent' || $name == 'user-agent') {
					if (str_contains($value, 'Aspen LiDA') || str_contains($value, 'aspen lida')) {
						return true;
					}
				}
			}
		}
		return false;
	}

	protected function extractBasicAuthCredentials(): void {
		if (!isset($_SERVER['PHP_AUTH_USER']) && (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))) {
			$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
			if (preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
				$credentials = base64_decode($matches[1]);
				if (strpos($credentials, ':') !== false) {
					list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $credentials, 2);
				}
			}
		}
	}

	/**
	 * @return array
	 * @noinspection PhpUnused
	 */
	function loadUsernameAndPassword() {
		$username = $_REQUEST['username'] ?? '';
		$password = $_REQUEST['password'] ?? '';

		if (isset($_POST['username']) && isset($_POST['password'])) {
			$username = $_POST['username'];
			$password = $_POST['password'];
		}

		if (is_array($username)) {
			$username = reset($username);
		}
		if (is_array($password)) {
			$password = reset($password);
		}
		return [$username, $password];
	}

	function logPatronRequest($userId): void {
		if ($this->context == 'lida') {
			require_once ROOT_DIR . '/sys/SystemLogging/UserAppRequestLogEntry.php';
			UserAppRequestLogEntry::logRequest($userId, $_GET['action'], $_GET['method'], json_encode($_REQUEST), $this->getLiDAVersion());
		}
	}

	private $_userForAPICall = null;
	/**
	 * @return bool|User
	 */
	function getUserForApiCall() : bool|User {
		$this->extractBasicAuthCredentials();

		global $oAuthUser;
		if (isset($oAuthUser) && $oAuthUser !== false) {
			// Note: 'Use All API Endpoints' permission is checked in grantTokenAccess()

			if (empty($_REQUEST['language'])) {
				global $activeLanguage;
				global $translator;
				$userLanguage = new Language();
				$userLanguage->code = $oAuthUser->interfaceLanguage;
				if ($userLanguage->find(true)) {
					if ($userLanguage->code != $activeLanguage->code) {
						$activeLanguage = $userLanguage;
						$translator = new Translator('lang', $userLanguage->code);
					}
				}
			}

			return $oAuthUser;
		}

		if ($this->_userForAPICall === null) {
			[$username, $password] = $this->loadUsernameAndPassword();
			$user = UserAccount::validateAccount($username, $password);
			if ($user !== false && $user->source == 'admin') {
				//Admin users are not allowed with API calls
				$this->_userForAPICall = false;
				return $this->_userForAPICall;
			}

			//Set translations up based on the active user's desired language
			if (empty($_REQUEST['language']) && $user !== false) {
				global $activeLanguage;
				global $translator;
				$userLanguage = new Language();
				$userLanguage->code = $user->interfaceLanguage;
				if ($userLanguage->find(true)) {
					if ($userLanguage->code != $activeLanguage->code) {
						$activeLanguage = $userLanguage;
						$translator = new Translator('lang', $userLanguage->code);
					}
				}
			}

			if ($user !== false && $user->allowAppRequestLogging) {
				$this->logPatronRequest($user->id);
			}
			$this->_userForAPICall = $user;
		}

		return $this->_userForAPICall;
	}

	/**
	 * Returns valid sources for Aspen LiDA to return when making API requests for searching, browse categories, lists, etc.
	 * <ul>
	 *     <li><b>Adding new items here without proper testing can result in the app crashing and should only be updated when a source is confirmed to be working with LiDA.</b></li>
	 * </ul>
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function getValidSourcesForLiDA($context = 'browseCategory'): array {
		if ($context == 'search') {
			return [
				'event_assabet',
				'event_communico',
				'event_libcal',
				'library_calendar_event',
				'event_aspenEvent',
				'grouped_work'
			];
		} elseif ($context == 'list') {
			return [
				'GroupedWork',
				'Events',
				'Lists'
			];
		} else {
			return [
				'GroupedWork',
				'List',
				'Events'
			];
    }
  }

	protected function setActiveLanguage(): void {
		global $activeLanguage;
		if (isset($_GET['language'])) {
			$language = new Language();
			$language->code = $_GET['language'];
			if ($language->find(true)) {
				$activeLanguage = $language;
			}
		}
	}

	/**
	 * Get authentication info for OpenAPI authorization
	 * @return array ['user' => User|false, 'greenhouseAuth' => bool]
	 */
	protected function getAuthInfoForOpenAPI(): array {
		global $oAuthUser;
		
		// Check if already authenticated via OAuth
		if (isset($oAuthUser) && $oAuthUser !== false) {
			return ['user' => $oAuthUser, 'greenhouseAuth' => false];
		}
		
		// Try to authenticate via Basic auth
		// Use validateTokenCredentials() - OpenAPI authorizer handles permissions
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			if ($this->validateTokenCredentials()) {
				global $oAuthUser;
				if (isset($oAuthUser) && $oAuthUser !== false) {
					// OAuth authentication succeeded - we have a user
					return ['user' => $oAuthUser, 'greenhouseAuth' => false];
				} else {
					// Greenhouse/LiDA keys - authorized but no user
					return ['user' => false, 'greenhouseAuth' => true];
				}
			}
		}
		
		return ['user' => false, 'greenhouseAuth' => false];
	}
	
	/**
	 * @deprecated Use getAuthInfoForOpenAPI() instead
	 */
	protected function getAuthenticatedUserForOpenAPI() {
		$authInfo = $this->getAuthInfoForOpenAPI();
		return $authInfo['user'];
	}

	protected function sendErrorResponse(string $error, int $code, ?string $message = null): void {
		http_response_code($code);
		header('Cache-Control: no-cache, must-revalidate');
		
		$response = ['error' => $error];
		if ($message !== null) {
			$response['message'] = $message;
		}
		
		$output = json_encode($response);
		
		$method = $_GET['method'] ?? 'unknown';
		ExternalRequestLogEntry::logRequest(
			$this->apiName . '.' . $method,
			$_SERVER['REQUEST_METHOD'],
			$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			getallheaders(),
			'',
			$code,
			$output,
			[]
		);
		
		echo $output;
	}

	protected function executeAPIMethod(string $method, array $responseConfig = []): void {
		require_once ROOT_DIR . '/sys/SystemLogging/APIUsage.php';
		APIUsage::incrementStat($this->apiName, $method);
		
		// Set content type header (can be overridden by response config)
		$contentType = $responseConfig['contentType'] ?? 'application/json';
		header('Content-type: ' . $contentType);
		
		// Set cache control - default to 3 hours, override with cacheMaxAge or noCache
		if (!empty($responseConfig['noCache'])) {
			header('Cache-Control: no-cache, must-revalidate');
		} else {
			$cacheMaxAge = $responseConfig['cacheMaxAge'] ?? 10800; // Default 3 hours
			header('Cache-Control: max-age=' . (int)$cacheMaxAge);
		}
		
		// Check if this is a raw response (no JSON wrapping)
		$isRaw = !empty($responseConfig['raw']);
		
		if ($isRaw) {
			// For raw responses, let the method handle output directly
			// (e.g., getLogoFile outputs binary data and calls die())
			$result = $this->$method();
			
			// If method returned something and didn't die(), output it
			if ($result !== null && $result !== '') {
				// Add XML declaration if needed
				if (!empty($responseConfig['xmlDeclaration'])) {
					echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
				}
				$output = $result;
				echo $output;
			} else {
				$output = '';
			}
		} else {
			// Standard JSON response
			$output = json_encode(['result' => $this->$method()]);
			echo $output;
		}
		
		ExternalRequestLogEntry::logRequest(
			$this->apiName . '.' . $method,
			$_SERVER['REQUEST_METHOD'],
			$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			getallheaders(),
			'',
			$_SERVER['REDIRECT_STATUS'] ?? 200,
			$output,
			[]
		);
	}

	protected function launchWithOpenAPI(): void {
		$method = (isset($_GET['method']) && !is_array($_GET['method'])) ? $_GET['method'] : '';
		
		// Don't set Content-type here - let executeAPIMethod handle it based on response config
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		
		$this->setActiveLanguage();
		$this->extractOAuthCredentials();
		$this->extractBasicAuthCredentials();
		
		$authInfo = $this->getAuthInfoForOpenAPI();
		$user = $authInfo['user'];
		$greenhouseAuth = $authInfo['greenhouseAuth'];
		$ipAllowed = IPAddress::allowAPIAccessForClientIP();
		
		require_once ROOT_DIR . '/sys/API/OpenAPIAuthorizer.php';
		$authResult = OpenAPIAuthorizer::authorize($this->apiName, $method, $user, $ipAllowed, $greenhouseAuth);
		
		if (!$authResult['allowed']) {
			header('Content-type: application/json');
			$this->sendErrorResponse($authResult['error'], $authResult['code'], $authResult['message'] ?? null);
			return;
		}
		
		$this->authorizedUser = $user;
		$this->authorizedScope = $authResult['scope'] ?? 'user';
		
		if (!method_exists($this, $method)) {
			header('Content-type: application/json');
			$this->sendErrorResponse('method_not_implemented', 501, "Method '$method' is defined but not implemented");
			return;
		}
		
		// Get response configuration from OpenAPI spec
		$responseConfig = OpenAPIAuthorizer::getResponseConfig($this->apiName, $method);
		
		$this->executeAPIMethod($method, $responseConfig);
	}

	protected function getAuthorizedUser() {
		return $this->authorizedUser;
	}

	protected function getAuthorizedScope(): string {
		return $this->authorizedScope;
	}

	protected function isSuperuserScope(): bool {
		return $this->authorizedScope === 'superuser';
	}

	/**
	 * Parse pagination parameters from the request.
	 *
	 * @param int $defaultPageSize Default number of items per page (default 100)
	 * @param int $maxPageSize     Maximum allowed page size (default 100)
	 * @return array{page: int, pageSize: int, offset: int}
	 */
	protected function getPaginationParams(int $defaultPageSize = 100, int $maxPageSize = 100): array {
		$page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
		$pageSize = isset($_REQUEST['pageSize']) ? min($maxPageSize, max(1, (int)$_REQUEST['pageSize'])) : $defaultPageSize;
		$offset = ($page - 1) * $pageSize;

		return [
			'page' => $page,
			'pageSize' => $pageSize,
			'offset' => $offset,
		];
	}

	/**
	 * Apply pagination to a DataObject query and return a paginated result set.
	 *
	 * The DataObject should have any filter properties (e.g. deleted, private, locationId)
	 * set BEFORE calling this method. The method will count matching rows, validate the
	 * requested page, apply limit/offset + orderBy, then iterate with fetch() and pass
	 * each row to the provided callback.
	 *
	 * @param DataObject $dataObject   Pre-filtered DataObject (filters set, not yet find()'d)
	 * @param string     $orderBy      SQL ORDER BY clause (e.g. 'title ASC, id DESC')
	 * @param callable   $formatRow    Callback that receives a DataObject row and returns an
	 *                                 array (the formatted item) or null to skip the row
	 * @param int        $defaultPageSize Default items per page
	 * @param int        $maxPageSize     Maximum allowed page size
	 * @return array Standardised response with pagination metadata and items
	 */
	protected function paginateQuery(DataObject $dataObject, string $orderBy, callable $formatRow, int $defaultPageSize = 100, int $maxPageSize = 100): array {
		$params = $this->getPaginationParams($defaultPageSize, $maxPageSize);
		$page = $params['page'];
		$pageSize = $params['pageSize'];
		$offset = $params['offset'];

		$totalResults = $dataObject->count();
		$totalPages = $totalResults > 0 ? (int)ceil($totalResults / $pageSize) : 0;

		if ($page <= $totalPages) {
			$dataObject->limit($offset, $pageSize);
			$dataObject->orderBy($orderBy);
			$dataObject->find();
		}

		$items = [];
		while ($dataObject->fetch()) {
			$formatted = $formatRow($dataObject);
			if ($formatted !== null) {
				$items[] = $formatted;
			}
		}

		return [
			'success' => true,
			'totalResults' => $totalResults,
			'page' => $page,
			'pageSize' => $pageSize,
			'totalPages' => $totalPages,
			'items' => $items,
		];
	}
}
