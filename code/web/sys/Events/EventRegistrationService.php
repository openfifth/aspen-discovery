<?php

require_once ROOT_DIR . '/sys/Events/EventInstance.php';
require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';

/**
 * Service class for handling event registration logic
 * Shared between patron self-registration and staff registration
 */
class EventRegistrationService {

	/**
	 * Register a user for an event instance
	 * @param int $userId The user to register
	 * @param int $eventInstanceId The event instance to register for
	 * @param int|null $staffUserId The staff user performing the registration (null for self-registration)
	 * @return array Result with success status and message
	 */
	public static function registerUserForEvent(int $userId, int $eventInstanceId, ?int $staffUserId = null): array {
		$result = [
			'success' => false,
			'title' => translate(['text' => 'Error', 'isPublicFacing' => true]),
			'message' => translate(['text' => 'Unknown error occurred.', 'isPublicFacing' => true]),
		];

		$eventInstance = new EventInstance();
		$eventInstance->id = $eventInstanceId;
		if (!$eventInstance->find(true)) {
			$result['message'] = translate(['text' => 'Event not found.', 'isPublicFacing' => true]);
			return $result;
		}

		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->id = $userId;
		if (!$user->find(true)) {
			$result['message'] = translate(['text' => 'User not found.', 'isPublicFacing' => true]);
			return $result;
		}

		$registration = new UserAspenEventInstanceRegistration();
		$registration->userId = $userId;
		$registration->eventInstanceId = $eventInstanceId;

		if ($registration->find(true)) {
			if (!$registration->cancelled) {
				$result['message'] = translate(['text' => 'User is already registered for this event.', 'isPublicFacing' => true]);
				return $result;
			}
			$registration->cancelled = 0;
			$registration->registeredByStaffId = $staffUserId;
			if ($registration->update()) {
				$result['success'] = true;
				$result['title'] = translate(['text' => 'Registration Successful', 'isPublicFacing' => true]);
				$result['message'] = translate(['text' => 'User has been registered for this event.', 'isPublicFacing' => true]);
			} else {
				$result['message'] = translate(['text' => 'Failed to update registration.', 'isPublicFacing' => true]);
			}
			return $result;
		}

		if (!$eventInstance->hasAvailableSeats(1)) {
			$result['message'] = translate(['text' => 'This event is full. No seats available.', 'isPublicFacing' => true]);
			return $result;
		}

		$registration->cancelled = 0;
		$registration->registeredByStaffId = $staffUserId;

		if ($registration->insert()) {
			$result['success'] = true;
			$result['title'] = translate(['text' => 'Registration Successful', 'isPublicFacing' => true]);
			$result['message'] = translate(['text' => 'User has been registered for this event.', 'isPublicFacing' => true]);

			self::addEventToUserSavedEvents($userId, $eventInstanceId);
		} else {
			$result['message'] = translate(['text' => 'Failed to create registration.', 'isPublicFacing' => true]);
		}

		return $result;
	}

	/**
	 * Unregister a user from an event instance
	 * @param int $userId The user to unregister
	 * @param int $eventInstanceId The event instance to unregister from
	 * @return array Result with success status and message
	 */
	public static function unregisterUserFromEvent(int $userId, int $eventInstanceId): array {
		$result = [
			'success' => false,
			'title' => translate(['text' => 'Error', 'isPublicFacing' => true]),
			'message' => translate(['text' => 'Unknown error occurred.', 'isPublicFacing' => true]),
		];

		$registration = new UserAspenEventInstanceRegistration();
		$registration->userId = $userId;
		$registration->eventInstanceId = $eventInstanceId;

		if (!$registration->find(true)) {
			$result['message'] = translate(['text' => 'Registration not found.', 'isPublicFacing' => true]);
			return $result;
		}

		if ($registration->cancelled) {
			$result['message'] = translate(['text' => 'Registration is already cancelled.', 'isPublicFacing' => true]);
			return $result;
		}

		$registration->cancelled = 1;
		if ($registration->update()) {
			$result['success'] = true;
			$result['title'] = translate(['text' => 'Registration Cancelled', 'isPublicFacing' => true]);
			$result['message'] = translate(['text' => 'Registration has been cancelled successfully.', 'isPublicFacing' => true]);
		} else {
			$result['message'] = translate(['text' => 'Failed to cancel registration.', 'isPublicFacing' => true]);
		}

		return $result;
	}

	/**
	 * Get all registrations for an event instance
	 * @param int $eventInstanceId The event instance ID
	 * @param bool $includesCancelled Whether to include cancelled registrations
	 * @return array Array of UserAspenEventInstanceRegistration objects
	 */
	public static function getRegistrationsForEvent(int $eventInstanceId, bool $includesCancelled = false): array {
		$registrations = [];
		$registration = new UserAspenEventInstanceRegistration();
		$registration->eventInstanceId = $eventInstanceId;
		if (!$includesCancelled) {
			$registration->whereAdd('cancelled IS NULL OR cancelled = 0');
		}
		$registration->find();
		while ($registration->fetch()) {
			$registrations[] = clone $registration;
		}
		return $registrations;
	}

	/**
	 * Check if a user has permission to register other users for events
	 * @return bool
	 */
	public static function canStaffRegisterUsers(): bool {
		global $library;
		if (empty($library->allowStaffToRegisterUsersForEvents)) {
			return false;
		}
		return UserAccount::userHasPermission([
			'Register Users for Events for All Locations',
			'Register Users for Events for Home Library Locations',
			'Register Users for Events for Home Location',
		]);
	}

	/**
	 * Check if staff can register users for a specific event location
	 * @param int $locationId The location ID of the event
	 * @return bool
	 */
	public static function canStaffRegisterUsersForLocation(int $locationId): bool {
		if (!self::canStaffRegisterUsers()) {
			return false;
		}

		if (UserAccount::userHasPermission('Register Users for Events for All Locations')) {
			return true;
		}

		$user = UserAccount::getLoggedInUser();
		if (!$user) {
			return false;
		}

		if (UserAccount::userHasPermission('Register Users for Events for Home Library Locations')) {
			$patronLibrary = Library::getLibraryForLocation($user->homeLocationId);
			$eventLibrary = Library::getLibraryForLocation($locationId);
			return $patronLibrary && $eventLibrary && $patronLibrary->libraryId == $eventLibrary->libraryId;
		}

		if (UserAccount::userHasPermission('Register Users for Events for Home Location')) {
			if ($user->homeLocationId == $locationId) {
				return true;
			}
			$additionalLocations = $user->getAdditionalAdministrationLocations();
			return array_key_exists($locationId, $additionalLocations);
		}

		return false;
	}

	/**
	 * Add event to user's saved events
	 * @param int $userId The user ID
	 * @param int $eventInstanceId The event instance ID
	 */
	private static function addEventToUserSavedEvents(int $userId, int $eventInstanceId): void {
		require_once ROOT_DIR . '/sys/Events/AspenEventSetting.php';
		require_once ROOT_DIR . '/RecordDrivers/AspenEventRecordDriver.php';

		$aspenEventSettings = new AspenEventSetting();
		$aspenEventSettings->id = 1;
		if (!$aspenEventSettings->find(true)) {
			return;
		}

		$sourceId = 'aspenEvent_' . $aspenEventSettings->id . '_' . $eventInstanceId;
		$recordDriver = new AspenEventRecordDriver($sourceId);
		if (!$recordDriver->isValid()) {
			return;
		}

		$recordDriver->saveUserEventEntry($sourceId, $userId);
	}

	/**
	 * Look up a user by barcode
	 * @param string $barcode The patron barcode
	 * @return array Result with user data or error
	 */
	public static function lookupUserByBarcode(string $barcode): array {
		$result = [
			'success' => false,
			'title' => translate(['text' => 'Error', 'isAdminFacing' => true]),
			'message' => translate(['text' => 'User not found.', 'isAdminFacing' => true]),
		];

		if (empty($barcode)) {
			$result['message'] = translate(['text' => 'Barcode is required.', 'isAdminFacing' => true]);
			return $result;
		}

		require_once ROOT_DIR . '/sys/Account/User.php';
		$user = new User();
		$user->ils_barcode = $barcode;

		if ($user->find(true)) {
			$result['success'] = true;
			$result['title'] = translate(['text' => 'User Found', 'isAdminFacing' => true]);
			$result['message'] = translate(['text' => 'User found successfully.', 'isAdminFacing' => true]);
			$result['user'] = [
				'id' => $user->id,
				'displayName' => $user->getDisplayName(),
				'barcode' => $user->ils_barcode,
				'email' => $user->email,
				'homeLocation' => $user->getHomeLocationName(),
			];
			return $result;
		}

		require_once ROOT_DIR . '/CatalogFactory.php';
		$catalog = CatalogFactory::getCatalogConnectionInstance(null, null);
		if (method_exists($catalog, 'findNewUser')) {
			$newUser = $catalog->findNewUser($barcode, '');
			if ($newUser && !($newUser instanceof AspenError)) {
				$newUser->getDisplayName();
				$newUser->update();
				$result['success'] = true;
				$result['title'] = translate(['text' => 'User Found', 'isAdminFacing' => true]);
				$result['message'] = translate(['text' => 'User loaded from ILS.', 'isAdminFacing' => true]);
				$result['user'] = [
					'id' => $newUser->id,
					'displayName' => $newUser->getDisplayName(),
					'barcode' => $newUser->ils_barcode,
					'email' => $newUser->email,
					'homeLocation' => $newUser->getHomeLocationName(),
				];
				return $result;
			}
		}

		$result['message'] = translate(['text' => 'User not found in Aspen or ILS.', 'isAdminFacing' => true]);
		return $result;
	}
}
