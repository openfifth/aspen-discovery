<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance.php';

class UserAspenEventInstanceRegistration extends DataObject {
	public $__table = 'user_aspen_event_instance_registrations';
	public $id;
	public $userId;
	public $eventInstanceId;
	public $status;
	public $createdAt;
	public $notifiedAt;

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

	/**
	 * Returns IDs of invited registrations whose invite window has expired.
	 *
	 * Batches by EventType so each type's waitingListInviteExpiryHours is honoured
	 * in a single query per type. Skips soft-deleted event instances.
	 *
	 */
	public static function getExpiredInvitedRowIds(): array {
		require_once ROOT_DIR . '/sys/Events/EventType.php';

		$eventType = new EventType();
		$eventType->find();

		$expiredIds = [];
		while ($eventType->fetch()) {
			$expiryHours = (int)$eventType->waitingListInviteExpiryHours;
			if ($expiryHours <= 0) {
				$expiryHours = 24;
			}
			$cutoff = date('Y-m-d H:i:s', time() - ($expiryHours * 3600));

			$query = new UserAspenEventInstanceRegistration();
			$query->status = 'invited';
			$query->whereAdd('notifiedAt IS NOT NULL');
			$query->whereAdd('notifiedAt < ' . $query->escape($cutoff));
			$query->whereAdd('eventInstanceId IN (SELECT ei.id FROM event_instance ei JOIN event e ON ei.eventId = e.id WHERE ei.deleted = 0 AND e.eventTypeId = ' . (int)$eventType->id . ')');
			$query->find();
			while ($query->fetch()) {
				$expiredIds[] = (int)$query->id;
			}
		}

		return $expiredIds;
	}

	/**
	 * Checks whether a user has at least one event instance to register to.
	*/
	static function isUserInvitedToRegister(int $userId): bool {
		$registration = new UserAspenEventInstanceRegistration();
		$registration->userId = $userId;
		$registration->status = 'invited';
		return $registration->find(true);
	}
}
