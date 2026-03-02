<?php
require_once ROOT_DIR . '/services/API/AbstractAPI.php';

class AspenEventInstanceAPI extends AbstractAPI {

	function launch(): void {
		$this->launchWithOpenAPI();
	}

	/**
	 * Returns a paginated list of event instances (occurrences) from public Aspen native events.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>eventId - Optional. Filter instances by event ID.</li>
	 * <li>startDate - Optional. Only return instances on or after this date (YYYY-MM-DD).</li>
	 * <li>endDate - Optional. Only return instances on or before this date (YYYY-MM-DD).</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getEventInstances(): array {
		require_once ROOT_DIR . '/sys/Events/EventInstance.php';

		$instance = new EventInstance();
		$instance->deleted = 0;
		$instance->whereAdd("eventId IN (SELECT id FROM event WHERE deleted = 0 AND private = 0)");

		$this->applyInstanceFilters($instance);

		return $this->paginateQuery($instance, 'date ASC, time ASC', function ($row) {
			return $row->toApiResponse();
		}, 20, 100);
	}

	/**
	 * Returns a paginated list of all event instances including those from private events.
	 * Permissions are enforced by the OpenAPI spec via x-aspen-authorization.
	 * Results are scoped based on the user's permission tier.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>eventId - Optional. Filter instances by event ID.</li>
	 * <li>startDate - Optional. Only return instances on or after this date (YYYY-MM-DD).</li>
	 * <li>endDate - Optional. Only return instances on or before this date (YYYY-MM-DD).</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getPrivateEventInstances(): array {
		require_once ROOT_DIR . '/sys/Events/EventInstance.php';

		$user = $this->getAuthorizedUser();
		$instance = new EventInstance();
		$instance->deleted = 0;

		if ($user->hasPermission('View Private Events for All Locations')) {
			$instance->whereAdd("eventId IN (SELECT id FROM event WHERE deleted = 0)");
		} elseif ($user->hasPermission('View Private Events for Home Library Locations')) {
			$locationsInLibrary = array_keys(Location::getLocationList(true));
			$locationList = implode(", ", $locationsInLibrary);
			$instance->whereAdd("eventId IN (SELECT id FROM event WHERE deleted = 0 AND (private = 0 OR locationId IN ($locationList)))");
		} else {
			$locations = array_keys($user->getAdditionalAdministrationLocations());
			$locations[] = $user->homeLocationId;
			$locationList = implode(", ", $locations);
			$instance->whereAdd("eventId IN (SELECT id FROM event WHERE deleted = 0 AND (private = 0 OR locationId IN ($locationList)))");
		}

		$this->applyInstanceFilters($instance);

		return $this->paginateQuery($instance, 'date ASC, time ASC', function ($row) {
			return $row->toApiResponse();
		}, 20, 100);
	}

	private function applyInstanceFilters(EventInstance $instance): void {
		if (!empty($_REQUEST['eventId'])) {
			$instance->eventId = $_REQUEST['eventId'];
		}
		if (!empty($_REQUEST['startDate'])) {
			$instance->whereAdd("date >= " . $instance->escape($_REQUEST['startDate']));
		}
		if (!empty($_REQUEST['endDate'])) {
			$instance->whereAdd("date <= " . $instance->escape($_REQUEST['endDate']));
		}
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
