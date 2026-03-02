<?php
require_once ROOT_DIR . '/services/API/AbstractAPI.php';

class AspenEventInstanceAPI extends AbstractAPI {

	function launch(): void {
		$this->launchWithOpenAPI();
	}

	/**
	 * Returns a paginated list of public events with their instances nested.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>eventId - Optional. Filter by event ID.</li>
	 * <li>startDate - Optional. Only include events with instances on or after this date (YYYY-MM-DD).</li>
	 * <li>endDate - Optional. Only include events with instances on or before this date (YYYY-MM-DD).</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getEventInstances(): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$event = new Event();
		$event->deleted = 0;
		$event->private = 0;

		$dateFilters = $this->getDateFilters();
		$this->applyEventFilters($event, $dateFilters);

		return $this->paginateQuery($event, 'title ASC', function ($row) use ($dateFilters) {
			return $row->toHarvestApiResponse($dateFilters);
		}, 20, 100);
	}

	/**
	 * Returns a paginated list of all events (including private) with their instances nested.
	 * Permissions are enforced by the OpenAPI spec via x-aspen-authorization.
	 * Results are scoped based on the user's permission tier.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>eventId - Optional. Filter by event ID.</li>
	 * <li>startDate - Optional. Only include events with instances on or after this date (YYYY-MM-DD).</li>
	 * <li>endDate - Optional. Only include events with instances on or before this date (YYYY-MM-DD).</li>
	 * </ul>
	 */
	/* @noinspection PhpUnused */
	function getPrivateEventInstances(): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$user = $this->getAuthorizedUser();
		$event = new Event();
		$event->deleted = 0;

		if ($user->hasPermission('View Private Events for All Locations')) {
			// No additional filter — see all events
		} elseif ($user->hasPermission('View Private Events for Home Library Locations')) {
			$locationsInLibrary = array_keys(Location::getLocationList(true));
			$locationList = implode(", ", $locationsInLibrary);
			$event->whereAdd("private = 0 OR locationId IN ($locationList)");
		} else {
			$locations = array_keys($user->getAdditionalAdministrationLocations());
			$locations[] = $user->homeLocationId;
			$locationList = implode(", ", $locations);
			$event->whereAdd("private = 0 OR locationId IN ($locationList)");
		}

		$dateFilters = $this->getDateFilters();
		$this->applyEventFilters($event, $dateFilters);

		return $this->paginateQuery($event, 'title ASC', function ($row) use ($dateFilters) {
			return $row->toHarvestApiResponse($dateFilters);
		}, 20, 100);
	}

	private function getDateFilters(): array {
		$filters = [];
		if (!empty($_REQUEST['startDate'])) {
			$filters['startDate'] = $_REQUEST['startDate'];
		}
		if (!empty($_REQUEST['endDate'])) {
			$filters['endDate'] = $_REQUEST['endDate'];
		}
		return $filters;
	}

	private function applyEventFilters(Event $event, array $dateFilters): void {
		if (!empty($_REQUEST['eventId'])) {
			$event->id = $_REQUEST['eventId'];
		}
		if (!empty($dateFilters)) {
			$subquery = "id IN (SELECT eventId FROM event_instance WHERE deleted = 0";
			if (!empty($dateFilters['startDate'])) {
				$subquery .= " AND date >= " . $event->escape($dateFilters['startDate']);
			}
			if (!empty($dateFilters['endDate'])) {
				$subquery .= " AND date <= " . $event->escape($dateFilters['endDate']);
			}
			$subquery .= ")";
			$event->whereAdd($subquery);
		}
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
