<?php

require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Events/EventRegistrationService.php';
require_once ROOT_DIR . '/sys/Events/EventInstance.php';
require_once ROOT_DIR . '/sys/Events/Event.php';

class Events_EventManagement extends Admin_Admin {

	function launch() {
		global $interface;

		$eventInstanceId = $_REQUEST['eventInstanceId'] ?? null;

		if (empty($eventInstanceId)) {
			$this->displayEventSelector();
			return;
		}

		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			$interface->assign('error', translate(['text' => 'Event not found.', 'isAdminFacing' => true]));
			$this->display('eventManagement.tpl', 'Event Management');
			return;
		}

		$parentEvent = $eventInstance->getParentEvent();

		if (!EventRegistrationService::canStaffRegisterUsersForLocation($parentEvent->locationId)) {
			$interface->assign('error', translate(['text' => 'You do not have permission to manage registrations for this event.', 'isAdminFacing' => true]));
			$this->display('eventManagement.tpl', 'Event Management');
			return;
		}

		if (isset($_GET['download_list']) && $_GET['download_list'] === 'true') {
			$this->downloadRegistrationsList($eventInstanceId);
			return;
		}

		if (isset($_GET['download_csv']) && $_GET['download_csv'] === 'true') {
			$this->downloadRegistrationsCSV($eventInstanceId);
			return;
		}

		$interface->assign('eventInstanceId', $eventInstanceId);
		$interface->assign('eventTitle', $parentEvent->title);
		$interface->assign('eventDate', $eventInstance->date);
		$interface->assign('eventTime', $eventInstance->time);
		$interface->assign('eventLocation', $eventInstance->getLocation());
		$interface->assign('numberOfSeats', $eventInstance->getEffectiveNumberOfSeats());
		$interface->assign('availableSeats', $eventInstance->getAvailableSeats());
		$interface->assign('registrationCount', $eventInstance->getRegistrationCount());

		$registrations = EventRegistrationService::getRegistrationsForEvent((int)$eventInstanceId, true);
		$registrationData = [];
		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$staffUser = $registration->getStaffUser();
			$registrationData[] = [
				'id' => $registration->id,
				'userId' => $registration->userId,
				'userName' => $user ? $user->getDisplayName() : 'Unknown',
				'userBarcode' => $user ? $user->ils_barcode : '',
				'userEmail' => $user ? $user->email : '',
				'cancelled' => (bool)$registration->cancelled,
				'attended' => (bool)$registration->attended,
				'registeredByStaff' => $registration->wasRegisteredByStaff(),
				'staffName' => $staffUser ? $staffUser->getDisplayName() : null,
				'dateRegistered' => $registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : null,
			];
		}
		$interface->assign('registrations', $registrationData);

		$this->display('eventManagement.tpl', 'Event Management - ' . $parentEvent->title);
	}

	private function displayEventSelector(): void {
		global $interface;
		global $library;

		if (empty($library->allowStaffToRegisterUsersForEvents)) {
			$interface->assign('featureDisabled', true);
			$this->display('eventManagement.tpl', 'Event Management');
			return;
		}

		$user = UserAccount::getLoggedInUser();
		$upcomingEvents = $this->getUpcomingEventsForUser($user);
		$interface->assign('upcomingEvents', $upcomingEvents);
		$interface->assign('showEventSelector', true);

		$this->display('eventManagement.tpl', 'Event Management');
	}

	private function getUpcomingEventsForUser($user): array {
		$events = [];
		$todayDate = date('Y-m-d');
		$todayTime = date('H:i:s');

		$eventInstance = new EventInstance();
		$eventInstance->whereAdd("(deleted IS NULL OR deleted = 0)");
		$eventInstance->whereAdd("(date > '$todayDate' OR (date = '$todayDate' AND time > '$todayTime'))");
		$eventInstance->orderBy('date ASC, time ASC');
		$eventInstance->limit(0, 100);

		$eventInstance->find();
		while ($eventInstance->fetch()) {
			$parentEvent = $eventInstance->getParentEvent();
			if ($parentEvent && !$parentEvent->deleted && $parentEvent->registrationRequired) {
				// Only show events with registration enabled that staff has location access to
				if (EventRegistrationService::canStaffRegisterUsersForLocation($parentEvent->locationId)) {
					$events[] = [
						'instanceId' => $eventInstance->id,
						'title' => $parentEvent->title,
						'date' => $eventInstance->date,
						'time' => $eventInstance->time,
						'location' => $eventInstance->getLocation(),
						'registrationCount' => $eventInstance->getRegistrationCount(),
						'availableSeats' => $eventInstance->getAvailableSeats(),
						'numberOfSeats' => $eventInstance->getEffectiveNumberOfSeats(),
						'registrationRequired' => (bool)$parentEvent->registrationRequired,
					];
				}
			}
		}

		return $events;
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#events', 'Events');
		$breadcrumbs[] = new Breadcrumb('/Events/EventManagement', 'Event Management');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'events';
	}

	function canView(): bool {
		return  UserAccount::userHasPermission([
			'Register Users for Events for All Locations',
			'Register Users for Events for Home Library Locations',
			'Register Users for Events for Home Location',
		]);
	}

	private function downloadRegistrationsList($eventInstanceId) {
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$parentEvent = $eventInstance->getParentEvent();
		$registrations = EventRegistrationService::getRegistrationsForEvent((int)$eventInstanceId, true);

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="registrations_list_' . $eventInstanceId . '.txt"');

		echo "Event: " . $parentEvent->title . "\n";
		echo "Date: " . $eventInstance->date . " " . $eventInstance->time . "\n";
		echo "Location: " . $eventInstance->getLocation() . "\n";
		echo "Total Registrations: " . count($registrations) . "\n";
		echo str_repeat("=", 80) . "\n\n";

		// Table header
		echo str_pad("Patron Name", 30) . str_pad("ILS Barcode", 15) . str_pad("Email", 40) . str_pad("Post Code", 12) . str_pad("Date of Birth", 15) . str_pad("Status", 12) . str_pad("Registered By", 20) . str_pad("Date Registered", 20) . "Attended\n";
		echo str_repeat("-", 137) . "\n";

		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$user->loadContactInformation(); // so that we get the postcode
			$staffUser = $registration->getStaffUser();

			$patronName = $user ? $user->getDisplayName() : 'Unknown';
			$barcode = $user ? $user->ils_barcode : '';
			$email = $user ? $user->email : '';
			$postcode = $user ? $user->_zip : '';
			$dateOfBirth = $user ? $user->_dateOfBirth : '';
			$status = $registration->cancelled ? 'Cancelled' : 'Active';
			$registeredBy = $registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self';
			$dateRegistered = $registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-';
			$attended = '[ ]';

			echo str_pad($patronName, 30) . str_pad($barcode, 15) . str_pad($email, 40) . str_pad($postcode, 12)  . str_pad($dateOfBirth, 15) . str_pad($status, 12) . str_pad($registeredBy, 20) . str_pad($dateRegistered, 20) . $attended . "\n";
		}
		exit;
	}

	private function downloadRegistrationsCSV($eventInstanceId) {
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$parentEvent = $eventInstance->getParentEvent();	
		$eventType = null;
		if(!empty($parentEvent)) {
			$eventType = $parentEvent->getEventType();
		}

		$registrations = EventRegistrationService::getRegistrationsForEvent((int)$eventInstanceId, true);

		$customFields = $this->getEventCustomFields($parentEvent);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="registrations_' . $eventInstanceId . '.csv"');

		$output = fopen('php://output', 'w');

		$headers = [
			'Event date',
			'Event title',
			'Event start time',
			'Library location',
			'Event Type',
			'Patron Name',
			'ILS Barcode',
			'Email',
			'Post Code',
			'Date of Birth',
			'Status', 
			'Registered By',
			'Date Registered',
			'Attended'
		];
		
		foreach ($customFields as $field) {
			$headers[] = $field['label'];
		}

		fputcsv($output, $headers);

		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$user->loadContactInformation(); // so that we get the postcode
			$staffUser = $registration->getStaffUser();

			$row = [
				$eventInstance ? $eventInstance->date : '',
				$parentEvent ? $parentEvent->title : '',
				$eventInstance ? $eventInstance->time : '',
				$eventInstance ? $eventInstance->getLocation() : '',
				$eventType ? $eventType->title : '',
				$user ? $user->getDisplayName() : 'Unknown',
				$user ? $user->ils_barcode : '',
				$user ? $user->email : '',
				$user ? $user->_zip : '',
				$user ? $user->_dateOfBirth : '',
				$registration->cancelled ? 'Cancelled' : 'Active',
				$registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self',
				$registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-',
				$registration->attended ? 'Yes' : 'No'
			];

			$customRegistrationFieldValues = $this->getRegistrationFieldValues($registration->id) ?? [];
			$customInformationFieldValues = $this->getInformationFieldValues($eventInstance->eventId) ?? [];
			$customFieldValues = $customInformationFieldValues + $customRegistrationFieldValues;

			foreach ($customFields as $field) {
				// FIXME: change the way select values are saved as this does not handle field being modified after the event was created
				if ($field['fieldType'] == '3') {
					$allowableValues = explode("\n", $field['allowableValues']);
					$index = $customFieldValues[$field['id']];

					if (array_key_exists($index, $allowableValues)) { // prevents skipping index 0
						$row[] = $allowableValues[$index] ?? '';
					} else {
						$row[] = '';
					}
					continue;
				} 
				$row[] = $customFieldValues[$field['id']] ?? '';
			}

			fputcsv($output, $row);
		}

		fclose($output);
		exit;
	}

	private function getEventCustomFields($event) {
		global $logger;
		$fields = [];
		$fieldSetIds = [];

		if (!empty($event->eventRegistrationFieldSetId)) $fieldSetIds[] = $event->eventRegistrationFieldSetId;
		if (!empty($event->eventInformationFieldSetId)) $fieldSetIds[] = $event->eventInformationFieldSetId;
	
		if (empty($fieldSetIds) && !empty($event->eventTypeId)) {
			require_once ROOT_DIR . '/sys/Events/EventType.php';
			$eventType = new EventType();
			$eventType->id = $event->eventTypeId;
			if ($eventType->find(true)) {
				$fieldSetIds[] = $eventType->eventRegistrationFieldSetId;
				$fieldSetIds[] = $eventType->eventInformationFieldSetId;
			}
		}

		if (empty($fieldSetIds)) {
			return $fields;
		}

		require_once ROOT_DIR . '/sys/Events/EventFieldSet.php';
		require_once ROOT_DIR . '/sys/Events/EventFieldSetField.php';
		require_once ROOT_DIR . '/sys/Events/EventField.php';

		foreach ($fieldSetIds as $fieldSetId) {
			$fieldSet = new EventFieldSet();
			$fieldSet->id = $fieldSetId;

			if ($fieldSet->find(true)) {
				$fieldSetField = new EventFieldSetField();
				$fieldSetField->eventFieldSetId = $fieldSet->id;
				$fieldSetField->find();
	
				while ($fieldSetField->fetch()) {
					$eventField = new EventField();
					$eventField->id = $fieldSetField->eventFieldId;
					if ($eventField->find(true)) {
						$fields[] = [
							'id' => $eventField->id,
							'label' => $eventField->name,
							'fieldType' => $eventField->type,
							'allowableValues' => $eventField->allowableValues ?? '',
						];
					}
				}
			} else {
				global $logger;
				$logger->log("Field set not found with ID: " . $fieldSetId, Logger::LOG_ERROR);
			}
		}
		return $fields;
	}

	private function getRegistrationFieldValues($registrationId) {
		$values = [];

		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistrationEventField.php';

		$registrationField = new UserAspenEventInstanceRegistrationEventField();
		$registrationField->eventInstanceRegistrationId = $registrationId;
		$registrationField->find();

		while ($registrationField->fetch()) {
			$values[$registrationField->eventFieldId] = $registrationField->value;
		}
		return $values;
	}

	private function getInformationFieldValues($eventId) {
		$values = [];

		require_once ROOT_DIR . '/sys/Events/EventEventField.php';

		$informationField = new EventEventField();
		$informationField->eventId = $eventId;
		$informationField->find();

		while ($informationField->fetch()) {
			$values[$informationField->eventFieldId] = $informationField->value;
		}
		return $values;
	}
}
