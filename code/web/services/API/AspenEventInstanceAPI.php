<?php
require_once ROOT_DIR . '/services/API/AbstractAPI.php';

class AspenEventInstanceAPI extends AbstractAPI {
	protected bool $allowLegacyOAuthKeys = true;

	function launch(): void {
		$method = (isset($_GET['method']) && !is_array($_GET['method'])) ? $_GET['method'] : '';

		header('Content-type: application/json');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		$this->setLanguage();

		$this->handleAPIRequestAuto($method, 'event_api');
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
	 *
	 * @oauth true
	 * @token false
	 * @public false
	 * @scopes event:read
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
	 * Requires a user with permission to view private events; results are scoped
	 * based on the user's permission tier.
	 *
	 * Parameters:
	 * <ul>
	 * <li>page - Page number for pagination. Default is 1.</li>
	 * <li>pageSize - Number of results per page. Default is 20.</li>
	 * <li>eventId - Optional. Filter by event ID.</li>
	 * <li>startDate - Optional. Only include events with instances on or after this date (YYYY-MM-DD).</li>
	 * <li>endDate - Optional. Only include events with instances on or before this date (YYYY-MM-DD).</li>
	 * </ul>
	 *
	 * @oauth true
	 * @token false
	 * @public false
	 * @scopes event:read
	 */
	/* @noinspection PhpUnused */
	function getPrivateEventInstances(): array {
		require_once ROOT_DIR . '/sys/Events/Event.php';

		$user = $this->getUserForApiCall();
		$userIsValid = $user instanceof User;
		if (!$userIsValid) {
			http_response_code(401);
			return [
				'success' => false,
				'message' => 'A valid user is required to view private events.',
			];
		}

		$canViewPrivateEvents = $user->hasPermission([
			'View Private Events for All Locations',
			'View Private Events for Home Library Locations',
			'View Private Events for Home Location',
		]);
		if (!$canViewPrivateEvents) {
			http_response_code(403);
			return [
				'success' => false,
				'message' => 'You do not have permission to view private events.',
			];
		}

		$event = new Event();
		$event->deleted = 0;

		$canViewAllLocations = $user->hasPermission('View Private Events for All Locations');
		if (!$canViewAllLocations) {
			$this->applyLocationScope($event, $user);
		}

		$dateFilters = $this->getDateFilters();
		$this->applyEventFilters($event, $dateFilters);

		return $this->paginateQuery($event, 'title ASC', function ($row) use ($dateFilters) {
			return $row->toHarvestApiResponse($dateFilters);
		}, 20, 100);
	}

	private function applyLocationScope(Event $event, User $user): void {
		$canViewLibraryLocations = $user->hasPermission('View Private Events for Home Library Locations');
		if ($canViewLibraryLocations) {
			$visibleLocationIds = array_keys(Location::getLocationList(true));
		} else {
			$visibleLocationIds = array_keys($user->getAdditionalAdministrationLocations());
			$visibleLocationIds[] = $user->homeLocationId;
		}

		$locationList = implode(', ', $visibleLocationIds);
		$event->whereAdd("private = 0 OR locationId IN ($locationList)");
	}

	private function getDateFilters(): array {
		$filters = [];

		$startDateProvided = !empty($_REQUEST['startDate']);
		if ($startDateProvided) {
			$filters['startDate'] = $_REQUEST['startDate'];
		}

		$endDateProvided = !empty($_REQUEST['endDate']);
		if ($endDateProvided) {
			$filters['endDate'] = $_REQUEST['endDate'];
		}

		return $filters;
	}

	private function applyEventFilters(Event $event, array $dateFilters): void {
		$eventIdProvided = !empty($_REQUEST['eventId']);
		if ($eventIdProvided) {
			$event->id = $_REQUEST['eventId'];
		}

		$hasDateFilters = !empty($dateFilters);
		if (!$hasDateFilters) {
			return;
		}

		$subquery = "id IN (SELECT eventId FROM event_instance WHERE deleted = 0";
		$hasStartDate = !empty($dateFilters['startDate']);
		if ($hasStartDate) {
			$subquery .= " AND date >= " . $event->escape($dateFilters['startDate']);
		}
		$hasEndDate = !empty($dateFilters['endDate']);
		if ($hasEndDate) {
			$subquery .= " AND date <= " . $event->escape($dateFilters['endDate']);
		}
		$subquery .= ")";
		$event->whereAdd($subquery);
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
