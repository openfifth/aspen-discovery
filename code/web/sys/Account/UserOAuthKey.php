<?php

class UserOAuthKey extends DataObject {
	public $__table = 'user_oauth_keys';
	public $id;
	public $userId;
	public $keyName;
	public $clientId;
	public $clientSecret;
	public $created;
	public $lastUsed;
	public $isActive;

	private static $failedAttempts = [];
	private static $maxAttempts = 5;
	private static $lockoutTime = 900;

	public function getNumericColumnNames(): array {
		return [
			'id',
			'userId',
			'isActive',
		];
	}

	public static function validateCredentials(string $clientId, string $clientSecret): User|bool {
		if (self::isLockedOut($clientId)) {
			return false;
		}

		$oauthKey = new UserOAuthKey();
		$oauthKey->clientId = $clientId;
		$oauthKey->isActive = 1;

		$keyFound = $oauthKey->find(true);
		$secretMatches = $keyFound && password_verify($clientSecret, $oauthKey->clientSecret);
		if (!$secretMatches) {
			return self::rejectCredentials($clientId);
		}

		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $oauthKey->userId;
		if (!$user->find(true)) {
			return self::rejectCredentials($clientId);
		}

		unset(self::$failedAttempts[self::lockoutKey($clientId)]);
		$oauthKey->lastUsed = time();
		$oauthKey->update();

		return $user;
	}

	private static function lockoutKey(string $clientId): string {
		return 'oauth_' . $clientId;
	}

	private static function isLockedOut(string $clientId): bool {
		$lockoutKey = self::lockoutKey($clientId);
		if (!isset(self::$failedAttempts[$lockoutKey])) {
			return false;
		}

		$attempts = self::$failedAttempts[$lockoutKey];
		if ($attempts['count'] < self::$maxAttempts) {
			return false;
		}

		$lockoutExpired = time() - $attempts['first_attempt'] >= self::$lockoutTime;
		if ($lockoutExpired) {
			unset(self::$failedAttempts[$lockoutKey]);
			return false;
		}

		global $logger;
		$logger->log("OAuth key $clientId is locked out due to too many failed attempts", Logger::LOG_WARNING);
		return true;
	}

	private static function rejectCredentials(string $clientId): bool {
		$lockoutKey = self::lockoutKey($clientId);
		if (!isset(self::$failedAttempts[$lockoutKey])) {
			self::$failedAttempts[$lockoutKey] = [
				'count' => 1,
				'first_attempt' => time(),
			];
		} else {
			self::$failedAttempts[$lockoutKey]['count']++;
		}

		usleep(500000);
		return false;
	}
}
