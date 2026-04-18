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
}
