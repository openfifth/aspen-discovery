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
			if ($oAuthUser->source == 'admin') {
				return false;
			}

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

	protected function getAuthenticatedUserForOpenAPI() {
		global $oAuthUser;
		if (isset($oAuthUser) && $oAuthUser !== false) {
			if ($oAuthUser->source == 'admin') {
				return false;
			}
			return $oAuthUser;
		}
		
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			if ($this->grantTokenAccess()) {
				global $oAuthUser;
				if ($oAuthUser->source == 'admin') {
					return false;
				}
				return $oAuthUser;
			}
		}
		
		return false;
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

	protected function executeAPIMethod(string $method): void {
		require_once ROOT_DIR . '/sys/SystemLogging/APIUsage.php';
		APIUsage::incrementStat($this->apiName, $method);
		
		$output = json_encode(['result' => $this->$method()]);
		
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
		
		echo $output;
	}
}