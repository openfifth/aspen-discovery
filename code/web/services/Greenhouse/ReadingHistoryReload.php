<?php

require_once ROOT_DIR . '/services/Admin/Admin.php';

class Greenhouse_ReadingHistoryReload extends Admin_Admin {
	function launch(): void {
		global $interface;
		if (isset($_REQUEST['submit'])) {
			$barcodesRaw = trim($_REQUEST['barcodes']);
			if (!empty($barcodesRaw)) {
				$barcodes = preg_split("/\\r\\n|\\r|\\n/", $barcodesRaw);
				$reloadResults = [];
				foreach ($barcodes as $barcode) {
					$foundUserForBarcode = false;
					foreach (UserAccount::getAccountProfiles() as $name => $accountProfileInfo) {
						$userToReset = new User();
						$userToReset->source = $name;
						$userToReset->ils_barcode = $barcode;
						if ($userToReset->find(true)) {
							$foundUserForBarcode = true;
							$userToReset->initialReadingHistoryLoaded = false;
							// Force the reload when the cron job runs.
							$userToReset->forceReadingHistoryLoad = true;
							$userToReset->readingHistoryImportStartedAt = null;
							$userToReset->update();
						}
					}
					$reloadResults[] = [
						'barcode' => $barcode,
						'success' => $foundUserForBarcode,
					];
				}
			}
			else {
				$reloadResults[] = [
					'barcode' => "No valid barcodes were entered.",
					'success' => false,
				];
			}

			$interface->assign('reloadResults', $reloadResults);
		}

		$this->display('reloadReadingHistoryFromIls.tpl', 'Reload Reading History from ILS for User', false);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Greenhouse/Home', 'Greenhouse Home');
		$breadcrumbs[] = new Breadcrumb('', 'Reload Reading History for User');

		return $breadcrumbs;
	}

	function canView() {
		if (UserAccount::isLoggedIn()) {
			if (UserAccount::getActiveUserObj()->isAspenAdminUser()) {
				return true;
			}
		}
		return false;
	}

	function getActiveAdminSection(): string {
		return 'greenhouse';
	}
}