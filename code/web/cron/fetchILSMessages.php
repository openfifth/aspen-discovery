<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';

require_once ROOT_DIR . '/CatalogFactory.php';

require_once ROOT_DIR . '/sys/CronLogEntry.php';
$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Fetch ILS Messages';
$cronLogEntry->insert();

//Because this is run from cron, we will loop through all account profiles and update account notifications for
// each one where account notifications are enabled.
$accountProfiles = UserAccount::getAccountProfiles();
foreach ($accountProfiles as $accountProfileInfo) {
	/** @var AccountProfile $accountProfile */
	$accountProfile = $accountProfileInfo['accountProfile'];
	if ($accountProfile->enableFetchingIlsMessages) {
		$cronLogEntry->notes .= "Fetching ILS messages for account profile $accountProfile->name.";
		$ilsNotificationSetting = new ILSNotificationSetting();
		$ilsNotificationSetting->accountProfileId =  $accountProfile->id;
		if ($ilsNotificationSetting->find(true)) {
			$catalogDriver = trim($accountProfile->driver);
			if (!empty($catalogDriver)) {
				$catalog = CatalogFactory::getCatalogConnectionInstance($catalogDriver, $accountProfile);
				try {
					$catalog->updateAccountNotifications($ilsNotificationSetting, $cronLogEntry);
				} catch (PDOException $e) {
					$cronLogEntry->numErrors++;
					$cronLogEntry->notes .= "Could not update message queue for account profile $accountProfile->id.";
				}
			}
		}
	}
}

$cronLogEntry->endTime = time();
$cronLogEntry->update();

global $aspen_db;
$aspen_db = null;

die();