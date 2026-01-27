<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance.php';

class UserAspenEventInstanceRegistration extends DataObject {
	public $__table = 'user_aspen_event_instance_registrations';
	public $id;
	public $userId;
	public $eventInstanceId;
	public $success;
	public $attended;
	public $cancelled;
	public $registeredByStaffId;
	public $dateRegistered;

	public function isUserRegisteredForEvent(): bool {
		return $this->find(true) && !$this->cancelled;
	}

	public function getNumericColumnNames(): array {
		return [
			'userId',
			'eventInstanceId',
			'success',
			'attended',
			'cancelled',
			'registeredByStaffId',
			'dateRegistered',
		];
	}

	/**
	 * Get the user object for the registered patron
	 * @return User|false
	 */
	public function getUser(): User|false {
		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $this->userId;
		if ($user->find(true)) {
			return $user;
		}
		return false;
	}

	/**
	 * Get the staff user who registered this patron (if applicable)
	 * @return User|false
	 */
	public function getStaffUser(): User|false {
		if (empty($this->registeredByStaffId)) {
			return false;
		}
		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $this->registeredByStaffId;
		if ($user->find(true)) {
			return $user;
		}
		return false;
	}

	/**
	 * Get the event instance this registration is for
	 * @return EventInstance|false
	 */
	public function getEventInstance(): EventInstance|false {
		$eventInstance = new EventInstance();
		$eventInstance->id = $this->eventInstanceId;
		if ($eventInstance->find(true)) {
			return $eventInstance;
		}
		return false;
	}

	/**
	 * Check if this registration was made by staff
	 * @return bool
	 */
	public function wasRegisteredByStaff(): bool {
		return !empty($this->registeredByStaffId);
	}
}