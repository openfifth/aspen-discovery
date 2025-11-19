<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance.php';

class UserAspenEventInstanceRegistration extends DataObject {
	public $__table = 'user_aspen_event_instance_registrations';
	public $userId;
	public $eventInstanceId;
	public $success;
	public $attended;
	public $cancelled;

	public function isUserRegisteredForEvent(): bool {
		return $this->find() && !$this->cancelled;
	}
}