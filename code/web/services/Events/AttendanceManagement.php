<?php

require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Events/EventInstance.php';
require_once ROOT_DIR . '/sys/Events/Event.php';

class Events_AttendanceManagement extends Admin_Admin {

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
			$this->display('eventManagement.tpl', 'Attendance Management');
			return;
		}

		require_once ROOT_DIR . '/services/EventRegistrationService.php';
		$parentEvent = $eventInstance->getParentEvent();
		if (!EventRegistrationService::canStaffManagePatronEventAttendance($parentEvent->locationId)) {
			$interface->assign('error', translate(['text' => 'You do not have permission to manage patron attendance for this event.', 'isAdminFacing' => true]));
			$this->display('eventManagement.tpl', 'Attendance Management');
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

		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
		global $library;

		$interface->assign('eventInstanceId', $eventInstanceId);
		$interface->assign('eventTitle', $parentEvent->title);
		$interface->assign('eventDate', $eventInstance->date);
		$interface->assign('eventTime', $eventInstance->time);
		$interface->assign('eventLocation', $eventInstance->getLocation());
		$interface->assign('numberOfSeats', $eventInstance->getEffectiveNumberOfSeats());
		$interface->assign('availableSeats', EventRegistrationService::getAvailableSeats($eventInstance));
		$interface->assign('registrationCount', EventRegistrationService::getRegistrationCount((int)$eventInstance->id));
		$interface->assign('canManageEventRegistration', $library->allowEventRegistration && $library->allowStaffToRegisterUsersForEvents && EventRegistrationService::canStaffRegisterUsersForLocation($parentEvent->locationId));

		$registrations = UserAspenEventInstanceRegistration::getRegistrationsForEvent((int)$eventInstanceId);
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
				'attended' => (bool)$registration->attended,
				'registeredByStaff' => $registration->wasRegisteredByStaff(),
				'staffName' => $staffUser ? $staffUser->getDisplayName() : null,
				'dateRegistered' => $registration->createdAt ? date('Y-m-d H:i', strtotime($registration->createdAt)) : null,
			];
		}
		$interface->assign('registrations', $registrationData);

		$this->display('eventManagement.tpl', 'Attendance Management - ' . $parentEvent->title);
	}

	private function displayEventSelector(): void {
		global $interface;
		$upcomingEvents = $this->getUpcomingEventsForUser();
		$interface->assign('upcomingEvents', $upcomingEvents);
		$interface->assign('showEventSelector', true);

		$this->display('eventManagement.tpl', 'Attendance Management');
	}

	private function getUpcomingEventsForUser(): array {
		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
		require_once ROOT_DIR . '/services/EventRegistrationService.php';

		$events = [];

		$eventInstance = new EventInstance();
		$eventInstance->whereAdd("(deleted IS NULL OR deleted = 0)");
		EventInstance::addUpcomingWhereClause($eventInstance);
		$eventInstance->orderBy('date ASC, time ASC');
		$eventInstance->limit(0, 100);

		$eventInstance->find();
		while ($eventInstance->fetch()) {
			$parentEvent = $eventInstance->getParentEvent();
			if ($parentEvent && !$parentEvent->deleted && $parentEvent->registrationRequired) {
				// Only show events with registration enabled that staff has location access to
				if (EventRegistrationService::canStaffManagePatronEventAttendance($parentEvent->locationId)) {
					$events[] = [
						'instanceId' => $eventInstance->id,
						'title' => $parentEvent->title,
						'date' => $eventInstance->date,
						'time' => $eventInstance->time,
						'location' => $eventInstance->getLocation(),
						'registrationCount' => EventRegistrationService::getRegistrationCount((int)$eventInstance->id),
						'availableSeats' => EventRegistrationService::getAvailableSeats($eventInstance),
						'numberOfSeats' => $eventInstance->getEffectiveNumberOfSeats(),
						'registrationRequired' => (bool)$parentEvent->registrationRequired,
						'attendeeCategoryBreakdown' => EventRegistrationService::getAttendeeCategoryBreakdown((int)$eventInstance->id),
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
		$breadcrumbs[] = new Breadcrumb('/Events/AttendanceManagement', 'Attendance Management');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'events';
	}

	function canView(): bool {
		require_once ROOT_DIR . '/services/EventRegistrationService.php';
		return EventRegistrationService::canStaffManagePatronEventAttendance();
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
		echo str_pad("Patron Name", 30) . str_pad("Barcode", 15) . str_pad("Email", 40) . str_pad("Registered By", 20) . str_pad("Date Registered", 20) . "Attended\n";
		echo str_repeat("-", 137) . "\n";

		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$staffUser = $registration->getStaffUser();

			$patronName = $user ? $user->getDisplayName() : 'Unknown';
			$barcode = $user ? $user->ils_barcode : '';
			$email = $user ? $user->email : '';
			$registeredBy = $registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self';
			$dateRegistered = $registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-';
			$attended = '[ ]';

			echo str_pad($patronName, 30) . str_pad($barcode, 15) . str_pad($email, 40) . str_pad($registeredBy, 20) . str_pad($dateRegistered, 20) . $attended . "\n";
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
		$registrations = EventRegistrationService::getRegistrationsForEvent((int)$eventInstanceId, true);

		$customFields = $this->getEventRegistrationFields($parentEvent);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="registrations_' . $eventInstanceId . '.csv"');

		$output = fopen('php://output', 'w');

		$headers = [
			'Patron Name',
			'Barcode',
			'Email',
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
			$staffUser = $registration->getStaffUser();

			$row = [
				$user ? $user->getDisplayName() : 'Unknown',
				$user ? $user->ils_barcode : '',
				$user ? $user->email : '',
				$registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self',
				$registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-',
				$registration->attended ? 'Yes' : 'No'
			];

			$customFieldValues = $this->getRegistrationFieldValues($registration->id);
			foreach ($customFields as $field) {
				$row[] = $customFieldValues[$field['id']] ?? '';
			}

			fputcsv($output, $row);
		}

		fclose($output);
		exit;
	}

	private function getEventRegistrationFields($event) {
		global $logger;
		$fields = [];

		$fieldSetId = $event->eventRegistrationFieldSetId ?? null;
		
		if (empty($fieldSetId) && !empty($event->eventTypeId)) {
			require_once ROOT_DIR . '/sys/Events/EventType.php';
			$eventType = new EventType();
			$eventType->id = $event->eventTypeId;
			if ($eventType->find(true)) {
				$fieldSetId = $eventType->eventRegistrationFieldSetId ?? null;
			}
		}


		if (empty($fieldSetId)) {
			return $fields;
		}

		require_once ROOT_DIR . '/sys/Events/EventFieldSet.php';
		require_once ROOT_DIR . '/sys/Events/EventFieldSetField.php';
		require_once ROOT_DIR . '/sys/Events/EventField.php';

		$fieldSet = new EventFieldSet();
		$fieldSet->id = $fieldSetId;

		if ($fieldSet->find(true)) {
			
			$fieldSetField = new EventFieldSetField();
			$fieldSetField->eventFieldSetId = $fieldSet->id;
			$count = $fieldSetField->find();
			
			
			while ($fieldSetField->fetch()) {
				$eventField = new EventField();
				$eventField->id = $fieldSetField->eventFieldId;
				if ($eventField->find(true)) {
					$fields[] = [
						'id' => $eventField->id,
						'label' => $eventField->name,
						'fieldType' => $eventField->type
					];
				}
			}
		} else {
			global $logger;
			$logger->log("Field set not found with ID: " . $fieldSetId, Logger::LOG_ERROR);
		}
		return $fields;
	}



	private function getRegistrationFieldValues($registrationId) {
		$values = [];

		require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistrationEventField.php';

		$registrationField = new UserAspenEventInstanceRegistrationEventField();
		$registrationField->eventInstanceRegistrationId = $registrationId;
		$count = $registrationField->find();
		$logger->log("Looking for registration field values for registration ID: $registrationId, found $count records", Logger::LOG_ERROR);

		while ($registrationField->fetch()) {
			$values[$registrationField->eventFieldId] = $registrationField->value;
		}
		return $values;
	}
}
