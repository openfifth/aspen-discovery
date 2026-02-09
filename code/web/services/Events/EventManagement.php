<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/Events/EventRegistrationService.php';
require_once ROOT_DIR . '/sys/Events/EventInstance.php';
require_once ROOT_DIR . '/sys/Events/Event.php';

class Events_EventManagement extends Action {

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
		return EventRegistrationService::canStaffRegisterUsers();
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

		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$staffUser = $registration->getStaffUser();

			echo "Patron Name: " . ($user ? $user->getDisplayName() : 'Unknown') . "\n";
			echo "Barcode: " . ($user ? $user->ils_barcode : '') . "\n";
			echo "Email: " . ($user ? $user->email : '') . "\n";
			echo "Status: " . ($registration->cancelled ? 'Cancelled' :  'Active') . "\n";
			echo "Registered By: " . ($registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self') . "\n";
			echo "Date Registered: " . ($registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-') . "\n";
			echo "Attended: " . ($registration->attended ? 'Yes' : 'No') . "\n";
			echo str_repeat("-", 80) . "\n";
		}
		exit;
	}

	private function downloadRegistrationsCSV($eventInstanceId) {
		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			return;
		}

		$registrations = EventRegistrationService::getRegistrationsForEvent((int)$eventInstanceId, true);

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="registrations_' . $eventInstanceId . '.csv"');

		$output = fopen('php://output', 'w');

		fputcsv($output, [
			'Patron Name',
			'Barcode',
			'Email',
			'Status', 
			'Registered By',
			'Date Registered',
			'Attended'
		]);

		foreach ($registrations as $registration) {
			$user = $registration->getUser();
			$staffUser = $registration->getStaffUser();

			fputcsv($output, [
				$user ? $user->getDisplayName() : 'Unknown',
				$user ? $user->ils_barcode : '',
				$user ? $user->email : '',
				$registration->cancelled ? 'Cancelled' : 'Active',
				$registration->wasRegisteredByStaff() ? ($staffUser ? $staffUser->getDisplayName() : 'Staff') : 'Self',
				$registration->dateRegistered ? date('Y-m-d H:i', $registration->dateRegistered) : '-',
				$registration->attended ? 'Yes' : 'No'
			]);
		}
		fclose($output);
		exit;
	}
}
