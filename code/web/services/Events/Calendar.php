<?php


class Events_Calendar extends Action {
	function launch() {
		global $interface;
		global $timer;

		// Include Search Engine Class
		require_once ROOT_DIR . '/sys/SolrConnector/Solr.php';
		require_once ROOT_DIR . '/sys/Utils/DateUtils.php';

		$today = new DateTime();
		$useWeek = 0;

		$month = $_REQUEST['month'] ?? $today->format('m');
		$year = $_REQUEST['year'] ?? $today->format('Y');
		$week = $_REQUEST['week'] ?? null;
		if ($week !== null) {
			$useWeek = 1;
		}

		if (isset($_REQUEST['week'])) {
			$week = $_REQUEST['week'];
			$useWeek = 1;
		} else if (isset($_REQUEST['month'])) {
			$month = $_REQUEST['month'];
		} else {
			$month = $today->format('m');
		}
		if (isset($_REQUEST['year'])) {
			$year = $_REQUEST['year'];
		} else {
			$year = $today->format('Y');
		}
		$interface->assign("useWeek", $useWeek);
		if ($useWeek) {
			$paddedWeek = str_pad($week, 2, '0', STR_PAD_LEFT);
			$weekFilter = $year . '-' . $paddedWeek;
			$calendarStart = "{$year}W{$paddedWeek}";
			$calendarStartDay = strtotime($calendarStart . " - 1 days"); // So that the week starts on Sunday
			$calendarEndDay = strtotime($calendarStart . "+ 5 days");
			$formattedWeekYear = DateUtils::formatDateLocale($calendarStartDay, 'medium') . " - " . DateUtils::formatDateLocale($calendarEndDay, 'medium');
			$month = date("n", strtotime($calendarStart));
			$interface->assign('calendarMonth', $formattedWeekYear);
			$monthLink = "/Events/Calendar?month=$month&year=$year";
			$interface->assign("monthLink", $monthLink);

			$prevWeek = $week - 1;
			$prevYear = $year;
			$lastWeekLastYear = date('W', strtotime('December 28th ' . ($year - 1)));
			if ($prevWeek == 0) {
				$prevWeek = $lastWeekLastYear;
				$prevYear--;
			}
			$prevLink = "/Events/Calendar?week=$prevWeek&year=$prevYear";
			$interface->assign('prevLink', $prevLink);

			$nextWeek = $week + 1;
			$nextYear = $year;
			$lastWeekThisYear = date('W', strtotime('December 28th ' . $year));
			if ($nextWeek > $lastWeekThisYear) {
				$nextWeek = 1;
				$nextYear++;
			}
			$nextLink = "/Events/Calendar?week=$nextWeek&year=$nextYear";
			$interface->assign('nextLink', $nextLink);
		} else {
			$paddedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
			$monthFilter = $year . '-' . $paddedMonth;
			$calendarStart = "$paddedMonth/1/$year";
			$calendarStartDay = new DateTime($calendarStart);
			$formattedMonthYear = DateUtils::formatDateLocale($calendarStartDay, 'medium', 'none', 'MMMM yyyy');
			$week = (int)$calendarStartDay->format("W") + 1;
			$interface->assign('calendarMonth', $formattedMonthYear);
			$weekLink = "/Events/Calendar?week=$week&year=$year";
			$interface->assign("weekLink", $weekLink);

			$prevMonth = $month - 1;
			$prevYear = $year;
			if ($prevMonth == 0) {
				$prevMonth = 12;
				$prevYear--;
			}
			$prevLink = "/Events/Calendar?month=$prevMonth&year=$prevYear";
			$interface->assign('prevLink', $prevLink);

			$nextMonth = $month + 1;
			$nextYear = $year;
			if ($nextMonth == 13) {
				$nextMonth = 1;
				$nextYear++;
			}
			$nextLink = "/Events/Calendar?month=$nextMonth&year=$nextYear";
			$interface->assign('nextLink', $nextLink);
		}



		// Initialise from the current search globals
		/** @var SearchObject_EventsSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject('Events');
		$searchObject->init();
		$searchObject->setPrimarySearch(false);
		$searchObject->setLimit(1000);
		//We have a default hidden filter to only show events after today, needs to be cleared for calendars.
		$searchObject->clearHiddenFilters();

		global $library;
		$locations = Location::getLocationList(false, true);
		asort($locations);

		$selectedLocation = 'all';
		$eventsDefaultCalendarView = $library->eventsDefaultCalendarView ?? 0;

		if ($eventsDefaultCalendarView == 1 ) {
			if (UserAccount::isLoggedIn()) {
				$user = userAccount::getActiveUserObj();
				if ($user) {
					if ($user->getHomeLibrary() != null) {
						$selectedLocation = $user->getHomeLibrary();
					} else {
						$selectedLocation = $library;
					}
				}
			}
			$selectedCode = array_search($selectedLocation, $locations);
			if ($selectedCode !== false) {
				$selectedLocation = $selectedCode;
			} else {
				$selectedLocation = 'all';
			}
		} elseif ($eventsDefaultCalendarView == 2 ) {
			reset($locations);
			$selectedLocation = key($locations);
		} else {
			$selectedLocation = 'all';
		}

		if (isset($_REQUEST['location'])) {
			$selectedLocation = $_REQUEST['location'];
		}

		if ($selectedLocation != 'all' && !empty($selectedLocation)) {
			$branchName = $locations[$selectedLocation] ?? $selectedLocation;
			$searchObject->addHiddenFilter('branch', '"' . $branchName . '"');
		}

		$locationParam = '&location=' . urlencode($selectedLocation);
		if (isset($prevLink)) $prevLink .= $locationParam;
		if (isset($nextLink)) $nextLink .= $locationParam;
		if (isset($weekLink)) $weekLink .= $locationParam;
		if (isset($monthLink)) $monthLink .= $locationParam;

		$interface->assign('locations', $locations);
		$interface->assign('selectedLocation', $selectedLocation);

		
		if (isset($prevLink)) $interface->assign('prevLink', $prevLink);
		if (isset($nextLink)) $interface->assign('nextLink', $nextLink);
		if (isset($weekLink)) $interface->assign('weekLink', $weekLink);
		if (isset($monthLink)) $interface->assign('monthLink', $monthLink);
		

		//Instead we limit to just this month.
		if ($useWeek) {
			$searchObject->addHiddenFilter("event_week", '"' . $weekFilter . '"');
		} else {
			$searchObject->addHiddenFilter("event_month", '"' . $monthFilter . '"');
		}
		// Check permissions before showing private events
		if (!UserAccount::userHasPermission('View Private Events for All Locations')) {
			if (!UserAccount::userHasPermission([
				'View Private Events for Home Library Locations',
				'View Private Events for Home Location'
			])) {
				$searchObject->addHiddenFilter('-private', "private");
			} else {
				if (!UserAccount::userHasPermission('View Private Events for Home Library Locations')) {
					$user = UserAccount::getLoggedInUser();
					$locations = array_values($user->getAdditionalAdministrationLocations());
					$locations[] = $user->getHomeLocationName();
					$searchObject->addHiddenFilter('private', '("' . implode('" OR "private_', $locations) . '" OR "public")');
				} else {
					$locations = array_values(Location::getLocationList(true));
					$searchObject->addHiddenFilter('private', '("private_' . implode('" OR "private_', $locations) . '" OR "public")');
				}
			}
		}
		$searchObject->setSort('start_date_sort');

		$timer->logTime('Setup Search');

		// Process Search
		$result = $searchObject->processSearch(true, true);
		if ($result instanceof AspenError) {
			/** @var AspenError $result */
			AspenError::raiseError($result->getMessage());
		}
		$timer->logTime('Process Search');

		// Some more variables
		//   Those we can construct AFTER the search is executed, but we need
		//   no matter whether there were any results
		$interface->assign('lookfor', $searchObject->displayQuery());
		$interface->assign('searchType', $searchObject->getSearchType());
		// Will assign null for an advanced search
		$interface->assign('searchIndex', $searchObject->getSearchIndex());

		// 'Finish' the search... complete timers and log search history.
		$searchObject->close();

		$searchResults = $searchObject->getResultRecordSet();

		$defaultTimezone = new DateTimeZone(date_default_timezone_get());

		//Setup the calendar display
		//Get a list of weeks for the month
		$weeks = [];
		if ($useWeek) {
			$month = date("n", $calendarStartDay);
			$paddedMonth = date("m", $calendarStartDay);
			$dayNum = date("j", $calendarStartDay);
			$lastDayInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$maxDay = date("d", strtotime($calendarStart . " + 6 days"));
		} else {
			$dayNum = 1;
			$maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		}
		for ($i = 0; $i < 5; $i++) {
			$week = [
				'days' => [],
			];

			$startDayIndex = 0;
			if ($i == 0) {
				if ($useWeek) {
					$startDayIndex = 0;
				} else {
					$startDayIndex = $calendarStartDay->format('N');
				}
				for ($j = 0; $j < $startDayIndex; $j++) {
					$week['days'][] = [
						'day' => '',
						'fullDate' => '',
						'events' => [],
					];
				}
			}
			for ($j = $startDayIndex; $j < 7; $j++) {
				$eventDay = $year . '-' . $paddedMonth . '-' . str_pad($dayNum, 2, '0', STR_PAD_LEFT);
				$eventDate = new DateTime($eventDay);

				$eventDayObj = [
					'day' => $dayNum,
					'fullDate' => $eventDate->format('l, F jS'),
					'events' => [],
				];

				//Loop through search results to find events for this day
				foreach ($searchResults as $result) {
					if (in_array($eventDay, $result['event_day'])) {
						$startDate = new DateTime($result['start_date']);
						$startDate->setTimezone($defaultTimezone);
						$endDate = new DateTime($result['end_date']);
						$endDate->setTimezone($defaultTimezone);

						if (($endDate->getTimestamp() - $startDate->getTimestamp()) > 24 * 60 * 60) {
							$formattedTime = 'All day';
						} else {
							$timeRange = DateUtils::formatTimeRange($startDate, $endDate, '12');
							$parts = explode(' - ', $timeRange, 2);
							if (count($parts) === 2) {
								$formattedTime = $parts[0] . '<span class="end-time"> - ' . $parts[1] . '</span>';
							} else {
								$formattedTime = $timeRange;
							}
						}
						$isCancelled = false;
						if (array_key_exists('reservation_state', $result) && in_array('Cancelled', $result['reservation_state'])) {
							$isCancelled = true;
						}
						$url = "";
						if (preg_match('`^communico`', $result['id'])){
							$url = '/Communico/' . $result['id'] . '/Event';
						}
						elseif (preg_match('`^libcal`', $result['id'])){
							$url = '/Springshare/' . $result['id'] . '/Event';
						}
						elseif (preg_match('`^lc_`', $result['id'])){
							$url = '/LibraryMarket/' . $result['id'] . '/Event';
						}
						elseif (preg_match('`^assabet`', $result['id'])){
							$url = '/Assabet/' . $result['id'] . '/Event';
						}
						elseif (preg_match('`^aspen`', $result['id'])){
							$url = '/AspenEvents/' . $result['id'] . '/Event';
						}
						$eventDayObj['events'][] = [
							'id' => $result['id'],
							'title' => $result['title'],
							'link' => $url,
							'formattedTime' => $formattedTime,
							'isCancelled' => $isCancelled,
							'location' => $result['branch'][0],
						];
					}
				}
				$week['days'][] = $eventDayObj;

				$dayNum++;
				if ($useWeek) {
					if ($dayNum > $lastDayInMonth) {
						$dayNum = 1;
						$paddedMonth = str_pad($month + 1, 2, '0', STR_PAD_LEFT);
					}
					if ($paddedMonth == 13) {
						$paddedMonth = "01";
					}
				} else if ($dayNum > $maxDay) {
					break;
				}
			}
			$weeks[] = $week;
			if ($useWeek) {
				break;
			} else if (!$useWeek && $dayNum > $maxDay) {
				break;
			}
		}
		$interface->assign('weeks', $weeks);

		$headerImage = $this->getHeaderImage();
		$interface->assign('headerImage', $headerImage['image'] ?? '');
		$interface->assign('headerAlt', $headerImage['altText'] ?? '');

		$week = isset($_REQUEST['week']) ? (int)$_REQUEST['week'] : null;
		$month = isset($_REQUEST['month']) ? (int)$_REQUEST['month'] : (int)$today->format('m');
		$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : (int)$today->format('Y');

		$interface->assign('month', $month);
		$interface->assign('year', $year);
		$interface->assign('week', $week);
		$interface->assign('useWeek', $useWeek);

		if ($useWeek) {
			$this->display('calendar.tpl', 'Events Calendar ' . $formattedWeekYear, '');
		} else {
			$this->display('calendar.tpl', 'Events Calendar ' . $formattedMonthYear, '');
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#events', 'Events');
		$breadcrumbs[] = new Breadcrumb('/Events/Calendar', 'Events Calendar');
		return $breadcrumbs;
	}

	function getHeaderImage() {
		require_once ROOT_DIR . '/sys/Events/CalendarDisplaySetting.php';
		$setting = new CalendarDisplaySetting();
		$headerImage = [];
		if ($setting->find(true)) {
			$headerImage["image"] =  !empty($setting->cover) ? "/files/original/" . $setting->cover : '';
			$headerImage["altText"] =  $setting->altText ?? '';
		}
		return $headerImage;
	}
}