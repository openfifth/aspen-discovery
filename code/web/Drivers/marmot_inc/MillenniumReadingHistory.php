<?php

class MillenniumReadingHistory {
	/**
	 * @var Sierra $driver ;
	 */
	private Sierra $driver;

	public function __construct(Sierra $driver) {
		$this->driver = $driver;
	}

	/**
	 * Do an update or edit of reading history information.  Current actions are:
	 * deleteMarked
	 * deleteAll
	 * exportList
	 * optOut
	 *
	 * @param User $patron
	 * @param string $action The action to perform
	 * @param array $selectedTitles The titles to do the action on if applicable
	 * @return array|null
	 */
	function doReadingHistoryAction(User $patron, string $action, array $selectedTitles): ?array {
		//Load the reading history page
		$scope = $this->driver->getDefaultScope();
		$curl_url = $this->driver->getVendorOpacUrl() . "/patroninfo~S$scope/" . $patron->unique_ils_id . "/readinghistory";

		$cookie = tempnam(sys_get_temp_dir(), "CURLCOOKIE");
		$curl_connection = curl_init($curl_url);
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl_connection, CURLOPT_UNRESTRICTED_AUTH, true);
		curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($curl_connection, CURLOPT_COOKIESESSION, true);
		curl_setopt($curl_connection, CURLOPT_POST, true);
		$post_data = $this->driver->_getLoginFormValues($patron);
		$post_items = [];
		foreach ($post_data as $key => $value) {
			$post_items[] = $key . '=' . urlencode($value);
		}
		$post_string = implode('&', $post_items);
		curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
		$loginResult = curl_exec($curl_connection);

		//When a library uses Encore, the initial login does a redirect and requires additional parameters.
		if (preg_match('/<input type="hidden" name="lt" value="(.*?)" \/>/si', $loginResult, $loginMatches)) {
			//Get the lt value
			$lt = $loginMatches[1];
			//Login again
			$post_data['lt'] = $lt;
			$post_data['_eventId'] = 'submit';
			$post_items = [];
			foreach ($post_data as $key => $value) {
				$post_items[] = $key . '=' . $value;
			}
			$post_string = implode('&', $post_items);
			curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
			curl_exec($curl_connection);
		}

		if ($action == 'deleteMarked') {
			//Load patron page readinghistory/rsh with selected titles marked
			if (!isset($selectedTitles) || count($selectedTitles) == 0) {
				return null;
			}
			$titles = [];
			foreach ($selectedTitles as $titleId) {
				$titles[] = $titleId . '=1';
			}
			$title_string = implode('&', $titles);
			//Issue a get request to delete the item from the reading history.
			//Note: Millennium really does issue a malformed url, and it is required
			//to make the history delete properly.
			$curl_url = $this->driver->getVendorOpacUrl() . "/patroninfo~S$scope/" . $patron->unique_ils_id . "/readinghistory/rsh&" . $title_string;
			curl_setopt($curl_connection, CURLOPT_HTTPGET, true);
			curl_setopt($curl_connection, CURLOPT_URL, $curl_url);
			curl_exec($curl_connection);
		} elseif ($action == 'deleteAll') {
			//load patron page readinghistory/rah
			$curl_url = $this->driver->getVendorOpacUrl() . "/patroninfo~S$scope/" . $patron->unique_ils_id . "/readinghistory/rah";
			curl_setopt($curl_connection, CURLOPT_URL, $curl_url);
			curl_setopt($curl_connection, CURLOPT_HTTPGET, true);
			curl_exec($curl_connection);
		} elseif ($action == 'exportList') {
			//Leave this unimplemented for now.
		} elseif ($action == 'optOut') {
			//load patron page readinghistory/OptOut
			$curl_url = $this->driver->getVendorOpacUrl() . "/patroninfo~S$scope/" . $patron->unique_ils_id . "/readinghistory/OptOut";
			curl_setopt($curl_connection, CURLOPT_URL, $curl_url);
			curl_setopt($curl_connection, CURLOPT_HTTPGET, true);
			curl_exec($curl_connection);
			$patron->trackReadingHistory = false;
			$patron->update();
		} elseif ($action == 'optIn') {
			//load patron page readinghistory/OptIn
			$curl_url = $this->driver->getVendorOpacUrl() . "/patroninfo~S$scope/" . $patron->unique_ils_id . "/readinghistory/OptIn";
			curl_setopt($curl_connection, CURLOPT_URL, $curl_url);
			curl_setopt($curl_connection, CURLOPT_HTTPGET, true);
			curl_exec($curl_connection);
			$patron->trackReadingHistory = true;
			$patron->update();
		}
		curl_close($curl_connection);
		unlink($cookie);
		return null;
	}
}