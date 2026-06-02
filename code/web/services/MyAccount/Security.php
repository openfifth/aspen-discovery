<?php

require_once ROOT_DIR . '/CatalogConnection.php';
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';

class Security extends MyAccount {
	function launch() : void {
		global $interface;

		$twoFactor = UserAccount::has2FAEnabledForPType();
		$interface->assign('twoFactorEnabled', $twoFactor);
		if (UserAccount::isLoggedIn()) {
			$user = UserAccount::getActiveUserObj();

			$twoFactorAuthSetting = $user->getTwoFactorAuthenticationSetting();
			if ($twoFactorAuthSetting != null) {
				$isEnabled = $twoFactorAuthSetting->isEnabled;
				if ($isEnabled != 'notAvailable') {
					$interface->assign('twoFactorStatus', (int)$user->twoFactorStatus);
					$interface->assign('twoFactorMethodSetup', $user->twoFactorMethod);
					$interface->assign('twoFactorMethodAllowed', $twoFactorAuthSetting->allowedMethod);
					$interface->assign('showBackupCodes', false);
					$interface->assign('enableDeactivation', true);
					$interface->assign('userHasTOTP', $user->twoFactorMethod == 'totp');
					$userHasEmailCodes = false;

					$emailCode = new TwoFactorAuthCode();
					$emailCode->userId = $user->id;
					$emailCode->status = 'backup';
					if ($emailCode->find(true)) {
						$userHasEmailCodes = true;
					}

					$interface->assign('userHasEmailCodes', $userHasEmailCodes);

					// determine if the user needs to update 2FA method
					$migrationNeeded = false;
					if ($twoFactorAuthSetting->allowedMethod === 'totp' && $user->twoFactorMethod != 'totp' && $user->twoFactorStatus == '1') {
						$migrationNeeded = true;
						$interface->assign('migrationRequired', true);

					}

					$interface->assign('migrationMessage', translate([
						'text' => 'Your library has migrated to authenticator app-based two-factor authentication. Please set it up now to continue accessing your account.',
						'isPublicFacing' => true,
					]));

					if ($user->twoFactorStatus == '1' && !$migrationNeeded) {
						$interface->assign('showBackupCodes', true);
						require_once ROOT_DIR . '/sys/TwoFactorAuthCode.php';
						require_once ROOT_DIR . '/sys/TwoFactorAuthTOTPSecret.php';
						$backupCode = new TwoFactorAuthCode();
						$backupCodes = $backupCode->getBackups();
						$numBackupCodes = count($backupCodes);
						$interface->assign('backupCodes', $backupCodes);
						$interface->assign('numBackupCodes', $numBackupCodes);
						if ($isEnabled == 'mandatory') {
							$interface->assign('enableDeactivation', false);
						}
					}

				}
			}
		}

		$this->display('securityPage.tpl', 'Security');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Security');
		return $breadcrumbs;
	}
}