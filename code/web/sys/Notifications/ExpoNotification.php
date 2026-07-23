<?php
require_once ROOT_DIR . '/sys/Account/UserNotification.php';
require_once ROOT_DIR . '/sys/Account/UserNotificationToken.php';
require_once ROOT_DIR . '/sys/CurlWrapper.php';

class ExpoNotification extends DataObject {
	private CurlWrapper|null $expoCurlWrapper;

	public function sendExpoPushNotification($body, $pushToken, $userId, $notificationType) : void {
		global $logger;
		//https://docs.expo.dev/push-notifications/sending-notifications
		$bearerAuthToken = $this->getNotificationAccessToken();
		$url = "https://exp.host/--/api/v2/push/send";
		$this->expoCurlWrapper = new CurlWrapper();
		$headers = [
			'Host: exp.host',
			'Accept: application/json',
			'Accept-Encoding: gzip, deflate',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $bearerAuthToken
		];
		$this->expoCurlWrapper->addCustomHeaders($headers, true);
		$logger->log("Sending notification to Expo servers", Logger::LOG_ERROR);
		$response = $this->expoCurlWrapper->curlPostPage($url, json_encode($body));
		$notification = new UserNotification();
		$notification->userId = $userId;
		$notification->pushToken = $pushToken;
		$notification->notificationType = $notificationType;
		$notification->notificationDate = time();
		$notification->completed = 0;
		$notification->error = 0;
		if ($this->expoCurlWrapper->getResponseCode() == 200) {
			$json = json_decode($response, true);
			$data = $json['data'];
			if ($data['status'] != "error") {
				if ($data['id']) {
					$notification->receiptId = $data['id'];
				}
			} else {
				$error = $data['details'];
				$notification->error = 1;
				$notification->message = $error['error'] . ": " . $data['message'];
				if ($error['error'] == "DeviceNotRegistered") {
					// we need to delete the bad token to stop trying to send to it
					UserNotificationToken::deleteToken($pushToken);
				}
			}
		} else {
			global $logger;
			$logger->log('Error sending notification via Expo ' . $this->expoCurlWrapper->getResponseCode() . ' ' . $response, Logger::LOG_ERROR);
			$notification->error = 1;
			$notification->message = 'Error sending notification via Expo ' . $this->expoCurlWrapper->getResponseCode();
		}

		$notification->insert();
	}

	public function getExpoNotificationReceipt($receiptId) : void {
		//https://docs.expo.dev/push-notifications/sending-notifications/#push-receipt-errors
		$bearerAuthToken = $this->getNotificationAccessToken();
		$url = "https://exp.host/--/api/v2/push/getReceipts";
		$this->expoCurlWrapper = new CurlWrapper();
		$headers = [
			'Host: exp.host',
			'Accept: application/json',
			'Accept-Encoding: gzip, deflate',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $bearerAuthToken,
		];
		$this->expoCurlWrapper->addCustomHeaders($headers, true);
		$body = ['ids' => [$receiptId]];
		$response = $this->expoCurlWrapper->curlPostPage($url, json_encode($body));
		if ($this->expoCurlWrapper->getResponseCode() == 200) {
			$json = json_decode($response, true);
			$data = $json['data'];
			$notification = new UserNotification();
			$notification->receiptId = $receiptId;
			if ($notification->find(true)) {
				if (array_key_exists($receiptId, $data)) {
					$receiptResponse = $data[$receiptId];
					if($receiptResponse['status'] == "ok") {
						$notification->completed = 1;
					} else {
						$details = $receiptResponse['details'];
						$notification->error = 1;
						$notification->message = $details['error'] . ': ' . $receiptResponse['message'];
						if($details['error'] == 'DeviceNotRegistered') {
							// we need to delete the bad token to stop trying to send to it
							UserNotificationToken::deleteToken($notification->pushToken);
						}
					}
				} else {
					$error = $json['errors'];
					$notification->error = 1;
					$notification->message = $error['message'] ?? 'There was an unknown error with sending the notification.';
				}
				$notification->update();
			}
		} else {
			global $logger;
			$logger->log('Error fetching notification receipt via Expo ' . $this->expoCurlWrapper->getResponseCode() . ' ' . $response, Logger::LOG_ERROR);
		}
	}

	private static string|null|false $_notificationAccessToken = false;
	public function getNotificationAccessToken() : ?string {
		if (self::$_notificationAccessToken === false) {
			self::$_notificationAccessToken = null;
			//First check to see if we have branded app settings.
			//Note, this is only setup to have a single branded app setting per instance which will be the normal case.
			//But if we ever wanted to support multiple Aspen LiDA instances within a single Aspen installation, we would need to make the
			//notification tokens stored include the slug name
			require_once ROOT_DIR . '/sys/AspenLiDA/BrandedAppSetting.php';
			$brandedSettings = new BrandedAppSetting();
			if ($brandedSettings->find(true)) {
				if (!empty($brandedSettings->notificationAccessToken)) {
					self::$_notificationAccessToken =  $brandedSettings->notificationAccessToken;
				}
			}

			if (self::$_notificationAccessToken == null) {
				require_once ROOT_DIR . '/sys/SystemVariables.php';
				$systemVariables = SystemVariables::getSystemVariables();
				if ($systemVariables && !empty($systemVariables->greenhouseUrl)) {
					if ($result = file_get_contents($systemVariables->greenhouseUrl . '/API/GreenhouseAPI?method=getNotificationAccessToken')) {
						$data = json_decode($result, true);
						self::$_notificationAccessToken = $data['result']['token'] ?? $data['token'] ?? null;
					}
				} else {
					global $configArray;
					if ($result = file_get_contents($configArray['Site']['url'] . '/API/GreenhouseAPI?method=getNotificationAccessToken')) {
						$data = json_decode($result, true);
						self::$_notificationAccessToken = $data['result']['token'] ?? $data['token'] ?? null;
					}
				}
			}
		}

		return self::$_notificationAccessToken;
	}

	public function sendExpoTestPushNotification($title, $body, $pushToken) : array {
		$notificationBody = [
			'to' => $pushToken,
			'title' => $title,
			'body' => $body,
		];

		$bearerAuthToken = $this->getNotificationAccessToken();
		$url = 'https://exp.host/--/api/v2/push/send';
		$this->expoCurlWrapper = new CurlWrapper();
		$headers = [
			'Host: exp.host',
			'Accept: application/json',
			'Accept-Encoding: gzip, deflate',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $bearerAuthToken,
		];
		$this->expoCurlWrapper->addCustomHeaders($headers, true);

		global $logger;
		$logger->log('Sending test notification to Expo servers', Logger::LOG_ERROR);

		$response = $this->expoCurlWrapper->curlPostPage($url, json_encode($notificationBody));
		ExternalRequestLogEntry::logRequest('ExpoTestPushNotification.Send', 'POST', $url, $this->expoCurlWrapper->getHeaders(), json_encode($notificationBody), $this->expoCurlWrapper->getResponseCode(), $response, []);
		return json_decode($response, true);
	}

	public function getExpoTestPushNotificationReceipt($receiptId) : array {
		$bearerAuthToken = $this->getNotificationAccessToken();
		$url = 'https://exp.host/--/api/v2/push/getReceipts';
		$this->expoCurlWrapper = new CurlWrapper();
		$headers = [
			'Host: exp.host',
			'Accept: application/json',
			'Accept-Encoding: gzip, deflate',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $bearerAuthToken,
		];
		$this->expoCurlWrapper->addCustomHeaders($headers, true);

		$body = ['ids' => [$receiptId]];
		global $logger;
		$logger->log('Fetching test notification receipt from Expo servers', Logger::LOG_ERROR);
		$response = $this->expoCurlWrapper->curlPostPage($url, json_encode($body));



		ExternalRequestLogEntry::logRequest('ExpoTestPushNotification.Receipt', 'POST', $url, $this->expoCurlWrapper->getHeaders(), json_encode($body), $this->expoCurlWrapper->getResponseCode(), $response, []);
		return json_decode($response, true);

	}

}