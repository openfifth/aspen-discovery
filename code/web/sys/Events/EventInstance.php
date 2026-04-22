<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/Event.php';

class EventInstance extends DataObject {
	public $__table = 'event_instance';
	public $id;
	public $eventId;
	public $date;
	public $time;
	public $length;
	public $sublocationId;
	public $status;
	public $note;
	public $numberOfSeats;
	public $waitingList;
	public $waitingListNumberOfSeats;

	public $dateUpdated;
	public $deleted;

	private $_parentEvent = null;

	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		$sublocationList = Location::getEventSublocations(null);
		$sublocationList = [""] + $sublocationList;
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'eventId' => [
				'property' => 'eventId',
				'type' => 'text',
				'label' => 'Event Name',
				'description' => 'A name for the field',
				'hiddenByDefault' => true,
				'hideInLists' => true,
			],
			'date' => [
				'property' => 'date',
				'type' => 'date',
				'label' => 'Event Date',
				'description' => 'The event date',
			],
			'time' => [
				'property' => 'time',
				'type' => 'time',
				'label' => 'Event Time',
				'description' => 'The event Time',
			],
			'length' => [
				'property' => 'length',
				'type' => 'integer',
				'label' => 'Length (Minutes)',
				'description' => 'The event length in minutes',
			],
			'sublocationId' => [
				'property' => 'sublocationId',
				'type' => 'enum',
				'label' => 'Sublocation',
				'description' => 'Sublocation of the event',
				'values' => $sublocationList,
			],
			'note' => [
				'property' => 'note',
				'type' => 'text',
				'label' => 'Note',
				'description' => 'A note for this specific instance',
			],
			'numberOfSeats' => [
				'property' => 'numberOfSeats',
				'type' => 'integer',
				'label' => 'Number of Seats Override',
				'description' => 'Override capacity for this specific instance. Leave blank to use event default.',
				'min' => 0,
				'max' => 1000,
			],
			'waitingList' => [
				'property' => 'waitingList',
				'type' => 'checkbox',
				'label' => 'Waiting List Override',
				'description' => 'Override whether waiting list is enabled for this specific instance.',
			],
			'waitingListNumberOfSeats' => [
				'property' => 'waitingListNumberOfSeats',
				'type' => 'integer',
				'label' => 'Number of Seats on Waiting List Override',
				'description' => 'Override waiting list capacity for this specific instance.',
				'min' => 0,
				'max' => 1000,
			],
			'status' => [
				'property' => 'status',
				'type' => 'checkbox',
				'label' => 'Active',
				'default' => 1,
				'description' => 'Whether the event is active or cancelled',
			],
			'dateUpdated' => [
				'property' => 'dateUpdated',
				'label' => 'Date last updated',
				'type' => 'hidden',
				'hideInLists' => true,
			]
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	public function getNumericColumnNames(): array {
		return [
			'length',
			'dateUpdated',
			'numberOfSeats',
			'waitingListNumberOfSeats',
		];
	}

	public function update(string $context = '') : int|bool {
		$this->dateUpdated = time();
		if (isset($this->_changedFields) && count($this->_changedFields) > 0) {
			$this->_changedFields[] = 'dateUpdated';
		}
		return parent::update();
	}

	public function insert(string $context = '') : int|bool {
		$this->dateUpdated = time();
		return parent::insert();
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false, bool $suppressIndividualNotifications = false) : bool|int {
		if ($useWhere) {
			throw new InvalidArgumentException('EventInstance::delete does not support $useWhere = true. Delete instances individually.');
		}

		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';

		$shouldNotify = !$suppressIndividualNotifications && $this->isUpcoming();

		$affectedUsersByStatus = $shouldNotify
			? UserAspenEventInstanceRegistration::getUsersGroupedByStatusForInstance((int)$this->id)
			: [];

		$this->deleted = 1;
		$this->dateUpdated = time();
		$softDeleteResult = parent::update();
		if ($softDeleteResult === false) {
			return false;
		}

		UserAspenEventInstanceRegistration::deleteAllForEventInstance((int)$this->id);

		if ($shouldNotify) {
			$this->sendCancellationNotificationEmails([$this], $affectedUsersByStatus);
		}

		return $softDeleteResult;
	}

	public function fetch(): bool|DataObject|null {
		$return = parent::fetch();
		if ($return) {
			if (empty($this->sublocationId)) {
				$event = $this->getParentEvent();
				$this->sublocationId = $event->sublocationId;
			}
		}
		return $return;
	}

	private function formatEmailTemplateEventInstances(array $eventInstances): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$formatted = [];

		foreach ($eventInstances as $instance) {
			$event = new Event();
			$event->id = $instance->eventId;
			if (!$event->find(true)) {
				continue;
			}
			$humanEventDate = DateUtils::formatHumanDate($instance->date);
			$formatted[] = [
				'eventTitle' => $event->title,
				'eventDate' => $humanEventDate,
				'eventTime' => $instance->time,
			];
		}
		return $formatted;
	}

	function getParentEvent() : Event {
		if ($this->_parentEvent !== null && $this->_parentEvent->id == $this->eventId) {
			return $this->_parentEvent;
		}
		$event = new Event();
		$event->id = $this->eventId;
		$event->find(true);
		$this->_parentEvent = $event;
		return $event;
	}

	/** @noinspection PhpUnused */
	function getLocation() : string {
		$event = $this->getParentEvent();
		$location = new Location();
		$location->locationId = $event->locationId;
		$location->find(true);
		return $location->displayName;
	}

	/** @noinspection PhpUnused */
	function getSublocation() : string {
		$event = $this->getParentEvent();
		$sublocations = Location::getEventSublocations($event->locationId);
		if ($event->sublocationId) {
			$sublocation = $sublocations[$event->sublocationId];
		}
		return $sublocation ?? '';
	}

	function getSeries($onlyFuture = false) : array {
		$series = [];
		$eventInstances = new EventInstance();
		$eventInstances->eventId = $this->eventId;
		$eventInstances->deleted = 0;
		if ($onlyFuture) {
			$escapedDate = $eventInstances->escape($this->date);
			$escapedTime = $eventInstances->escape($this->time);
			$eventInstances->whereAdd("date > " . $escapedDate . " OR date = " . $escapedDate . " AND time > " . $escapedTime);
		} else {
			$eventInstances->whereAdd("id != " . $this->id);
		}
		$eventInstances->orderBy('date');
		$eventInstances->find();
		while ($eventInstances->fetch()) {
			$series[$eventInstances->id] = clone($eventInstances);
		}
		return $series;
	}

	function getUpcomingInstanceCount() {
		$event = $this->getParentEvent();
		return $event->getInstanceCount();
	}

	public function getEffectiveNumberOfSeats(): ?int {
		if ($this->numberOfSeats !== null && $this->numberOfSeats > 0) {
			return $this->numberOfSeats;
		}
		$event = $this->getParentEvent();
		if ($event->numberOfSeats === null || $event->numberOfSeats == 0) {
			return null;
		}
		return $event->numberOfSeats;
	}

	public function getRegistrationCount(): int {
		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
		$registration = new UserAspenEventInstanceRegistration();
		$registration->eventInstanceId = $this->id;
		$registration->status = 'registered';
		return $registration->count();
	}

	public function isWaitingListEnabled(): bool {
		if ($this->waitingList !== null) {
			return (bool)$this->waitingList;
		}
		$event = $this->getParentEvent();
		return (bool)$event->waitingList;
	}

	public function getEffectiveWaitingListNumberOfSeats(): ?int {
		if ($this->waitingListNumberOfSeats !== null && $this->waitingListNumberOfSeats > 0) {
			return $this->waitingListNumberOfSeats;
		}
		$event = $this->getParentEvent();
		if ($event->waitingListNumberOfSeats === null || $event->waitingListNumberOfSeats == 0) {
			return null;
		}
		return $event->waitingListNumberOfSeats;
	}

	public function getAvailableWaitingListSeats(): ?int {
		$capacity = $this->getEffectiveWaitingListNumberOfSeats();
		if ($capacity === null) {
			return null;
		}
		return max(0, $capacity - $this->getWaitingListCount());
	}

	public function getRegistrationStatusMessage(bool $waitingListEnabled, bool $userOnWaitingList, bool $canRegister, int $waitingListPosition, bool $isEventFull, bool $isWaitingListFull): string {
		if (!$waitingListEnabled) {
			return "Registration available";
		}

		if ($userOnWaitingList) {
			if ($canRegister) {
				return "Registration available";
			}
			return "On waiting list - position " . $waitingListPosition;
		}

		if (!$isEventFull) {
			return "Registration available";
		}

		if (!$isWaitingListFull) {
			return "Waiting List available";
		}

		return "Registration unavailable";
	}

	public function getRegistrationAction(bool $isRegistered, bool $isEventFull, bool $waitingListEnabled, bool $userOnWaitingList, bool $canRegister, bool $isWaitingListFull): string {
		if ($isRegistered) {
			return 'registered';
		}

		if (!$isEventFull) {
			return 'registrationAvailable';
		}

		if (!$waitingListEnabled) {
			return 'eventFull';
		}

		if ($userOnWaitingList && $canRegister) {
			return 'completeRegistration';
		}

		if ($userOnWaitingList) {
			return 'showPosition';
		}

		if (!$isWaitingListFull) {
			return 'joinWaitingList';
		}

		return 'eventFull';
	}


	public function getAvailableSeats(): ?int {
		$capacity = $this->getEffectiveNumberOfSeats();
		if ($capacity === null) {
			return null;
		}

		// If anyone is queued on the waiting list, block direct registration — new users must join the queue
		$waitingListCount = $this->getWaitingListCount();
		if ($waitingListCount > 0) {
			return 0;
		}

		return max(0, $capacity - $this->getRegistrationCount());
	}

	public function hasAvailableSeats(int $requestedSeats = 1): bool {
		$capacity = $this->getEffectiveNumberOfSeats();
		if ($capacity === null) {
			return true;
		}
		$available = $this->getAvailableSeats();
		return $available >= $requestedSeats;
	}


	public function getDisplayWaitingListSeats(): string {
		if ($this->deleted) {
			return '—';
		}

		$totalSeats = $this->getEffectiveWaitingListNumberOfSeats();
		if ($totalSeats === null) {
			return 'Available';
		}

		$available = $this->getAvailableWaitingListSeats();
		return "{$available} / {$totalSeats}";
	}

	public function getWaitingListCount(): int {
		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
		$registration = new UserAspenEventInstanceRegistration();
		$registration->eventInstanceId = $this->id;
		$registration->whereAdd('status IN ("waiting", "invited")');
		return $registration->count();
	}


	public function isUpcoming(): bool {
		if (empty($this->date) || empty($this->time)) {
			return false;
		}
		$eventTimestamp = strtotime($this->date . ' ' . $this->time);
		if ($eventTimestamp === false) {
			return false;
		}
		return $eventTimestamp > time();
	}

	public static function addUpcomingWhereClause(DataObject $query): void {
		$cutoffDate = $query->escape(date('Y-m-d'));
		$cutoffTime = $query->escape(date('H:i:s'));
		$query->whereAdd("(date > $cutoffDate OR (date = $cutoffDate AND time > $cutoffTime))");
	}

	public function isWaitingListFull(): bool {
		$capacity = $this->getEffectiveWaitingListNumberOfSeats();
		if ($capacity === null) {
			return false;
		}
		return $this->getWaitingListCount() >= $capacity;
	}

	public function inviteNextOnWaitingList(): bool {
		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
		require_once ROOT_DIR . '/sys/Account/User.php';
		global $logger;

		$candidateIds = UserAspenEventInstanceRegistration::getWaitingRowIdsForInstance((int)$this->id);

		foreach ($candidateIds as $candidateId) {
			$candidate = new UserAspenEventInstanceRegistration();
			$candidate->id = $candidateId;
			if (!$candidate->find(true)) {
				continue;
			}

			$user = new User();
			$user->id = $candidate->userId;
			if (!$user->find(true)) {
				$candidate->delete();
				$logger->log("Waiting list candidate removed — user {$candidate->userId} not found (instance {$this->id})", Logger::LOG_WARNING);
				continue;
			}

			if (!$user->canReceiveEventNotifications()) {
				$logger->log("Waiting list candidate skipped — user {$user->id} unreachable for event notifications (instance {$this->id})", Logger::LOG_WARNING);
				continue;
			}

			$candidate->status = 'invited';
			$candidate->notifiedAt = date('Y-m-d H:i:s');
			$candidate->update();

			$this->sendEventInstanceRegistrationInvitation((int)$candidate->userId);
			return true;
		}

		return false;
	}

	private function sendEventInstanceRegistrationInvitation(int $userId): void {
		$event = $this->getParentEvent();

		$homeLibrary = Library::getPatronHomeLibrary();
		if (is_null($homeLibrary)) {
			global $library;
			$homeLibrary = $library;
		}

		$this->sendEventEmail($userId, 'registerForEventFromWaitingList', [
			'eventDate' => DateUtils::formatHumanDate($this->date),
			'eventTime' => $this->time,
			'eventTitle' => $event->title,
			'library' => $homeLibrary,
		]);
	}

	public function sendEventEmail(int $userId, string $templateName, array $parameters): bool {
		require_once ROOT_DIR . '/sys/Email/Mailer.php';
		require_once ROOT_DIR . '/sys/Email/EmailTemplate.php';
		global $logger;

		$emailTemplate = EmailTemplate::getActiveTemplate($templateName);
		if (!$emailTemplate) {
			$logger->log("Unable to find email template: $templateName", Logger::LOG_ERROR);
			return false;
		}
		
		$user = new User();
		$user->id = $userId;
		if (!$user->find(true)) {
			$logger->log("$templateName email skipped — user $userId not found", Logger::LOG_ERROR);
			return false;
		}

		if (!$user->canReceiveEventNotifications()) {
			return false;
		}

		$parameters['user'] = $user;

		$sent = $emailTemplate->sendEmail($user->email, $parameters);
		if (!$sent) {
			$logger->log("$templateName email failed to send for user $userId", Logger::LOG_ERROR);
			return false;
		}
		return true;
	}

	public function saveToUserEvents(int $userId, int|null $savedByStaffId = null): void {
		require_once ROOT_DIR . '/sys/Events/AspenEventSetting.php';
		$setting = new AspenEventSetting();
		if (!$setting->find(true)) {
			return;
		}

		$sourceId = 'aspenEvent_' . $setting->id . '_' . $this->id;

		require_once ROOT_DIR . '/sys/Events/UserEventsEntry.php';
		$entry = new UserEventsEntry();
		$entry->sourceId = $sourceId;
		$entry->userId = $userId;
		if ($entry->find(true)) {
			return;
		}

		$event = $this->getParentEvent();

		$entry->title = mb_substr($event->title, 0, 50);
		$entry->eventDate = strtotime($this->date . ' ' . $this->time);
		$entry->regRequired = !empty($event->registrationRequired) ? 1 : 0;
		$entry->savedByStaffId = $savedByStaffId;

		require_once ROOT_DIR . '/sys/LibraryLocation/Location.php';
		$location = new Location();
		$location->locationId = $event->locationId;
		$entry->location = $location->find(true) ? $location->displayName : '';

		$entry->dateAdded = time();
		$entry->insert();
	}

	public function sendCancellationNotificationEmails(array $upcomingInstances, array $affectedUsersByStatus): void {
		if (empty($upcomingInstances) || empty($affectedUsersByStatus)) {
			return;
		}
		$formattedInstances = $this->formatEmailTemplateEventInstances($upcomingInstances);
		foreach ($affectedUsersByStatus as $status => $userIds) {
			foreach ($userIds as $userId) {
				$this->sendEventEmail($userId, 'eventCancellation', [
					'instances' => $formattedInstances,
					'status' => $status,
				]);
			}
		}
	}

}