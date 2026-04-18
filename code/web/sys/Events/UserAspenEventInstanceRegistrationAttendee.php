<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserAspenEventInstanceRegistrationAttendee extends DataObject {
	public $__table = 'user_aspen_event_instance_registration_attendee';
	public $id;
	public $registrationId;
	public $attendeeCategoryId;
	public $count;

	public function getUniquenessFields(): array {
		return ['registrationId', 'attendeeCategoryId'];
	}

	public function getNumericColumnNames(): array {
		return ['registrationId', 'attendeeCategoryId', 'count'];
	}
}
