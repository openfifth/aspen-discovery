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

	/**
	 * Validate attendee counts against the event type's category limits.
	 * Returns validated array or false if any count exceeds its max.
	 * Returns empty array if the event type has no categories.
	 */
	public static function validateAttendeeCounts(EventInstance $eventInstance, array $attendeeCounts): array|false {
		if (empty($attendeeCounts)) {
			return [];
		}

		$eventType = $eventInstance->getEventType();
		if ($eventType === null) {
			return [];
		}

		$attendeeCategoriesForEventType = $eventType->getEventTypeAttendeeCategories();
		if (empty($attendeeCategoriesForEventType)) {
			return [];
		}

		$maxByCategory = [];
		foreach ($attendeeCategoriesForEventType as $eventTypeAttendeeCategory) {
			$maxByCategory[(int)$eventTypeAttendeeCategory->attendeeCategoryId] = (int)$eventTypeAttendeeCategory->maxAttendees;
		}

		$validated = [];
		foreach ($attendeeCounts as $categoryId => $count) {
			$categoryId = (int)$categoryId;
			$count = (int)$count;

			if (!isset($maxByCategory[$categoryId])) {
				continue;
			}

			if ($count > $maxByCategory[$categoryId]) {
				return false;
			}

			if ($count > 0) {
				$validated[$categoryId] = $count;
			}
		}

		return $validated;
	}
}
