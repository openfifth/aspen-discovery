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
		$interface->assign('canManageEventRegistration', $library->allowEventRegistration && $library->allowStaffToRegisterUsersForEvents && EventRegistrationService::canStaffRegisterUsers($parentEvent->locationId));

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
				'registrationStatus' => $registration->status,
				'attendeeCategoryBreakdown' => EventRegistrationService::getRegistrationAttendeeCategoryBreakdown((int)$registration->id, (int)$eventInstanceId),
			];
		}
		$interface->assign('registrations', $registrationData);
		$interface->assign('hasAttendeeCategories', !empty($registrationData) && !empty($registrationData[0]['attendeeCategoryBreakdown']));

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

	private function buildBaseRegistrationRow(UserAspenEventInstanceRegistration $registration): array {
		$user = $registration->getUser();
		$staffUser = $registration->getStaffUser();
		return [
			$user ? $user->getDisplayName() : 'Unknown',
			$user ? $user->ils_barcode : '',
			$user ? $user->email : '',
			$registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self',
			$registration->createdAt ? date('Y-m-d H:i', strtotime($registration->createdAt)) : '-',
			$registration->status,
		];
	}

	private function downloadRegistrationsList($eventInstanceId) {
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$parentEvent = $eventInstance->getParentEvent();
		$registrations = UserAspenEventInstanceRegistration::getRegistrationsForEvent((int)$eventInstanceId, true);

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="registrations_list_' . $eventInstanceId . '.txt"');

		echo "Event: " . $parentEvent->title . "\n";
		echo "Date: " . $eventInstance->date . " " . $eventInstance->time . "\n";
		echo "Location: " . $eventInstance->getLocation() . "\n";
		echo "Total Registrations: " . count($registrations) . "\n";
		echo str_repeat("=", 80) . "\n\n";

		echo str_pad("Patron Name", 30) . str_pad("Barcode", 15) . str_pad("Email", 40) . str_pad("Registered By", 20) . str_pad("Date Registered", 20) . str_pad("Status", 15) . "Attended\n";
		echo str_repeat("-", 152) . "\n";

		foreach ($registrations as $registration) {
			[$patronName, $barcode, $email, $registeredBy, $dateRegistered, $registrationStatus] = $this->buildBaseRegistrationRow($registration);
			echo str_pad($patronName, 30) . str_pad($barcode, 15) . str_pad($email, 40) . str_pad($registeredBy, 20) . str_pad($dateRegistered, 20) . str_pad($registrationStatus, 15) . "[ ]\n";
		}
		exit;
	}

	private function downloadRegistrationsCSV($eventInstanceId) {
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$registrations = UserAspenEventInstanceRegistration::getRegistrationsForEvent((int)$eventInstanceId);

		$eventType = $eventInstance->getEventType();
		$customFields = $eventType !== null ? $eventType->getFieldSetFieldsByUse(2) : [];
		$attendeeCategories = $eventType !== null ? $eventType->getEventTypeAttendeeCategories() : [];

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="registrations_' . $eventInstanceId . '.csv"');

		$output = fopen('php://output', 'w');

		$headers = ['Patron Name', 'Barcode', 'Email', 'Registered By', 'Date Registered', 'Registration Status', 'Attended'];

		foreach ($customFields as $fieldId => $field) {
			$headers[] = $field['label'];
		}

		foreach ($attendeeCategories as $eventTypeCategory) {
			$category = $eventTypeCategory->getCategory();
			if ($category !== null) {
				$headers[] = $category->name . ' (attendees)';
			}
		}

		fputcsv($output, $headers);

		foreach ($registrations as $registration) {
			$row = [...$this->buildBaseRegistrationRow($registration), $registration->attended ? 'Yes' : 'No'];

			$customFieldValues = $registration->getCustomFieldValues();
			foreach ($customFields as $fieldId => $field) {
				$row[] = $customFieldValues[(int)$fieldId] ?? '';
			}

			$attendeeCounts = $registration->getAttendeeCounts();
			foreach ($attendeeCategories as $eventTypeCategory) {
				$category = $eventTypeCategory->getCategory();
				if ($category !== null) {
					$row[] = $attendeeCounts[(int)$eventTypeCategory->attendeeCategoryId] ?? 0;
				}
			}

			fputcsv($output, $row);
		}

		fclose($output);
		exit;
	}

}
