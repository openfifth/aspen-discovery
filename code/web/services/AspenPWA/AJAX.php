<?php

require_once ROOT_DIR . '/JSON_Action.php';

class AspenPWA_AJAX extends JSON_Action {
	function saveNotificationPushToken()
	{
		require_once ROOT_DIR . '/services/API/UserAPI.php';
		$api = new UserAPI('internal');
		return $api->saveNotificationPushToken();
	}

	function setNotificationPreference()
	{
		require_once ROOT_DIR . '/services/API/UserAPI.php';
		$api = new UserAPI('internal');
		return $api->setNotificationPreference();
	}
}