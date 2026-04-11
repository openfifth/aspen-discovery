<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance.php';

class UserAspenEventInstanceRegistration extends DataObject {
	public $__table = 'user_aspen_event_instance_registrations';
	public $id;
	public $userId;
	public $eventInstanceId;
	public $status;
	public $createdAt;

	const VALID_STATUSES = ['waiting', 'invited', 'registered'];

	public function getUniquenessFields(): array {
		return [
			'userId',
			'eventInstanceId',
		];
	}

	public function isUserRegisteredForEvent(): bool {
		if($this->status) {
			return $this->status === 'registered';
		}

		if (!$this->find(true)) {
			return false;
		}
		return $this->status === 'registered';
	}

	public function registerUser(): bool {
		$status = 'registered';

		if ($this->status === 'registered') {
			return false;
		}

		if (!$this->validateStatus($status)) {
			return false;
		}

		if ($this->find(true)) {
			$this->status = $status;
			return $this->update();
		}

		$this->status = $status;
		$this->createdAt = date('Y-m-d H:i:s');
		return $this->insert();
	}

	public function addUserToWaitingList(): bool {
		if ($this->find(true)) {
			return false;
		}

		$status = 'waiting';

		if (!$this->validateStatus($status)) {
			return false;
		}

		$this->createdAt = date('Y-m-d H:i:s');
		$this->status = $status;
		return $this->insert();
	}

	public function getWaitingListInfo(): array {
		$default = ['onWaitingList' => false, 'position' => null, 'canRegister' => false];

		if (!$this->status && !$this->find(true)) {
			return $default;
		}

		if (!in_array($this->status, ['waiting', 'invited'], true)) {
			return $default;
		}

		return [
			'onWaitingList' => true,
			'position' => self::getWaitingListPosition($this->eventInstanceId, $this->createdAt),
			'canRegister' => $this->status === 'invited',
		];
	}

	/**
	 * Static because DataObject uses set properties as implicit WHERE clauses.
	 * Calling this on an instance with userId set would contaminate the count
	 * query, returning only the caller's own rows instead of all queue entries.
	 */
	public static function getWaitingListPosition(int $eventInstanceId, string $createdAt): ?int {
		$query = new UserAspenEventInstanceRegistration();
		$query->eventInstanceId = $eventInstanceId;
		$query->whereAdd('status IN ("waiting", "invited")');
		$query->whereAdd("createdAt < " . $query->escape($createdAt));

		return $query->count() + 1;
	}

	private function validateStatus(string $status): bool {
		return in_array($status, self::VALID_STATUSES, true);
	}
}
