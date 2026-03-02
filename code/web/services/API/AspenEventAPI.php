<?php
require_once ROOT_DIR . '/services/API/AbstractAPI.php';

class AspenEventAPI extends AbstractAPI {

	function launch(): void {
		$this->launchWithOpenAPI();
	}

	/**
	 * Returns a list of public (non-private) Aspen native events.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>locationId - Optional. Filter events by location ID.</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getAspenEvents(): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$event = new Event();
		$event->deleted = 0;
		$event->private = 0;

		if (!empty($_REQUEST['locationId'])) {
			$event->locationId = $_REQUEST['locationId'];
		}

		return $this->paginateQuery($event, 'startDate ASC, startTime ASC', function ($row) {
			return $row->toApiResponse();
		}, 20, 100);
	}

	/**
	 * Returns a list of all Aspen native events including private events.
	 * Permissions are enforced by the OpenAPI spec via x-aspen-authorization.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>locationId - Optional. Filter events by location ID.</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getPrivateAspenEvents(): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$user = $this->getAuthorizedUser();
		$event = new Event();
		$event->deleted = 0;

		if (!empty($_REQUEST['locationId'])) {
			$event->locationId = $_REQUEST['locationId'];
		}

		if (!$user->hasPermission('View Private Events for All Locations')) {
			if ($user->hasPermission('View Private Events for Home Library Locations')) {
				$locationsInLibrary = array_keys(Location::getLocationList(true));
				$event->whereAdd("(private = 0 OR locationId IN (" . implode(", ", $locationsInLibrary) . "))");
			} else {
				$locations = array_keys($user->getAdditionalAdministrationLocations());
				$locations[] = $user->homeLocationId;
				$event->whereAdd("(private = 0 OR locationId IN (" . implode(", ", $locations) . "))");
			}
		}

		return $this->paginateQuery($event, 'startDate ASC, startTime ASC', function ($row) {
			return $row->toApiResponse();
		}, 20, 100);
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
