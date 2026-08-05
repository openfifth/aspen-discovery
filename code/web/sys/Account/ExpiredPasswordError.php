<?php /** @noinspection PhpMissingFieldTypeInspection */

class ExpiredPasswordError extends AspenError {
	public $userId;
	public $expirationDate;
	public $resetToken;

	public function __construct($userId = null, $expirationDate = null, $resetToken = null) {
		parent::__construct('Your PIN has expired.');
		$this->userId = $userId;
		$this->expirationDate = $expirationDate;
		$this->resetToken = $resetToken;
	}
}
