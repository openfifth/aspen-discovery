<?php

use Random\RandomException;

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
require_once ROOT_DIR . '/sys/UserAccount.php';
require_once ROOT_DIR . '/sys/OverDrive/OverDriveSetting.php';
require_once ROOT_DIR . '/sys/LibraryLocation/Library.php';
require_once ROOT_DIR . '/sys/Account/User.php';

class OverDrive_QRCodeAuth extends Action {
	private const string STATE_CACHE_PREFIX = 'overdrive_qr_state_';

	/**
	 * @throws RandomException
	 */
	function launch() : void {
		$operation = $_REQUEST['op'] ?? 'start';
		switch ($operation) {
			case 'start':
				$this->startQRCodeFlow();
				return;
			case 'complete':
				$this->handleAuthComplete();
				return;
			case 'abandon':
				$this->displayResult(false, translate([
					'text' => 'The authentication request was canceled before completion.',
					'isPublicFacing' => true,
				]));
				return;
			case 'error':
				$errorDetail = $_REQUEST['error'] ?? '';
				$this->displayResult(false, translate([
					'text' => 'OverDrive returned an error while authorizing the account.%1%',
					'1' => empty($errorDetail) ? '' : ' (' . $errorDetail . ')',
					'isPublicFacing' => true,
				]));
				return;
			case 'disconnect':
				$this->disconnectSession();
				return;
			default:
				$this->displayResult(false, translate([
					'text' => 'Unknown request.',
					'isPublicFacing' => true,
				]));
		}
	}

	/**
	 * @throws RandomException
	 */
	private function startQRCodeFlow(): void {
		$user = UserAccount::getLoggedInUser();
		if (!$user) {
			$this->displayResult(false, translate([
				'text' => 'Please sign in to link your Sora/OverDrive account.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$driver = new OverDriveDriver();
		$availableSettings = $driver->getAvailableSettings();
		if (empty($availableSettings)) {
			$this->displayResult(false, translate([
				'text' => 'No OverDrive collections are configured for this library.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$requestSettingId = isset($_REQUEST['settingId']) ? (int)$_REQUEST['settingId'] : null;
		if ($requestSettingId !== null && isset($availableSettings[$requestSettingId])) {
			$activeSetting = $availableSettings[$requestSettingId];
		} else {
			$activeSetting = reset($availableSettings);
		}

		if (empty($activeSetting->enableQRCodeAuth)) {
			$this->displayResult(false, translate([
				'text' => 'QR code authentication is not enabled for this OverDrive collection.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$homeLibrary = $user->getHomeLibrary();
		if ($homeLibrary == null) {
			$this->displayResult(false, translate([
				'text' => 'Unable to determine your library for OverDrive access.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$librarySettings = $homeLibrary->getLibraryOverdriveSetting($activeSetting->id);
		$credentials = $driver->getClientCredentials($activeSetting, $librarySettings);
		if (empty($credentials['clientKey']) || empty($credentials['clientSecret'])) {
			$this->displayResult(false, translate([
				'text' => 'OverDrive client credentials have not been configured.',
				'isPublicFacing' => true,
			]));
			return;
		}
		if (empty($activeSetting->websiteId)) {
			$this->displayResult(false, translate([
				'text' => 'The OverDrive website id is missing for this collection.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$state = bin2hex(random_bytes(16));
		global $memCache;
		$memCache->set(self::STATE_CACHE_PREFIX . $state, [
			'userId' => $user->id,
			'settingId' => $activeSetting->id,
			'libraryId' => $homeLibrary->libraryId,
		], 900);

		global $configArray;
		$baseUrl = rtrim($configArray['Site']['url'], '/');
		$redirectBase = $baseUrl . '/OverDrive/QRCodeAuth';
		$params = [
			'redirect_url' => $redirectBase . '?op=complete&state=' . $state,
			'abandon_url' => $redirectBase . '?op=abandon&state=' . $state,
			'error_url' => $redirectBase . '?op=error&state=' . $state,
			'website_id' => $activeSetting->websiteId,
			'client_id' => $credentials['clientKey'],
		];

		$target = 'https://oauth-patron.overdrive.com/device/initiate?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		header("Location: $target");
		exit();
	}

	private function handleAuthComplete(): void {
		$state = $_REQUEST['state'] ?? '';
		$code = $_REQUEST['code'] ?? '';
		if (empty($state) || empty($code)) {
			$this->displayResult(false, translate([
				'text' => 'Missing information from OverDrive.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$stateData = $this->getStateData($state);
		if ($stateData === null) {
			$this->displayResult(false, translate([
				'text' => 'Authentication session expired. Please try again.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$user = UserAccount::getLoggedInUser();
		if (!$user || $user->id != $stateData['userId']) {
			$user = new User();
			$user->id = $stateData['userId'];
			if (!$user->find(true)) {
				$this->displayResult(false, translate([
					'text' => 'Unable to load the user account for this session.',
					'isPublicFacing' => true,
				]));
				return;
			}
		}

		$setting = new OverDriveSetting();
		$setting->id = $stateData['settingId'];
		if (!$setting->find(true)) {
			$this->displayResult(false, translate([
				'text' => 'Unable to load the OverDrive collection for this request.',
				'isPublicFacing' => true,
			]));
			return;
		}

		if (empty($setting->enableQRCodeAuth)) {
			$this->displayResult(false, translate([
				'text' => 'This OverDrive collection does not allow QR code authentication.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$library = new Library();
		$library->libraryId = $stateData['libraryId'];
		if (!$library->find(true)) {
			$library = $user->getHomeLibrary();
			if (!$library) {
				$this->displayResult(false, translate([
					'text' => 'Unable to determine which library should be used for this OverDrive account.',
					'isPublicFacing' => true,
				]));
				return;
			}
		}

		$librarySettings = $library->getLibraryOverdriveSetting($setting->id);
		$driver = OverDriveDriver::getOverDriveDriver($setting->id);
		$tokenData = $driver->exchangeQRCodeAuthCode($setting, $librarySettings, $code);
		if (!$tokenData) {
			$this->displayResult(false, translate([
				'text' => 'OverDrive did not provide an access token. Please try again.',
				'isPublicFacing' => true,
			]));
			return;
		}

		$user->saveOverDriveQrToken($setting->id, $tokenData);
		$this->displayResult(true, translate([
			'text' => 'Success! Your OverDrive account is ready for one-click checkouts.',
			'isPublicFacing' => true,
		]));
	}

	private function disconnectSession(): void {
		$user = UserAccount::getLoggedInUser();
		if (!$user) {
			$this->displayResult(false, translate([
				'text' => 'Please sign in to change this setting.',
				'isPublicFacing' => true,
			]));
			return;
		}
		$settingId = isset($_REQUEST['settingId']) ? (int)$_REQUEST['settingId'] : 0;
		if ($settingId <= 0) {
			$this->displayResult(false, translate([
				'text' => 'Missing OverDrive setting identifier.',
				'isPublicFacing' => true,
			]));
			return;
		}
		$homeLibrary = $user->getHomeLibrary();
		$settings = $homeLibrary ? $homeLibrary->getOverdriveSettings() : [];
		if (!isset($settings[$settingId]) || empty($settings[$settingId]->enableQRCodeAuth)) {
			$this->displayResult(false, translate([
				'text' => 'QR code authentication is not available for this collection.',
				'isPublicFacing' => true,
			]));
			return;
		}
		$user->deleteOverDriveQrToken($settingId);
		$this->displayResult(true, translate([
			'text' => 'The saved Sora/OverDrive session has been removed.',
			'isPublicFacing' => true,
		]));
	}

	private function getStateData(string $state): ?array {
		global $memCache;
		$data = $memCache->get(self::STATE_CACHE_PREFIX . $state);
		if ($data !== false) {
			$memCache->delete(self::STATE_CACHE_PREFIX . $state);
			return $data;
		}
		return null;
	}

	private function displayResult(bool $success, string $message): void {
		global $interface;
		$readerNameDriver = new OverDriveDriver();
		$interface->assign('readerName', $readerNameDriver->getReaderName());
		$interface->assign('qrResultSuccess', $success);
		$interface->assign('qrResultMessage', $message);
		$pageTitle = translate([
			'text' => 'OverDrive Authentication',
			'isPublicFacing' => true,
		]);
		$interface->assign('qrResultTitle', $pageTitle);
		$this->display('OverDrive/qrCodeAuthResult.tpl', $pageTitle);
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
