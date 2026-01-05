<?php

class MillenniumHolds {
	/** @var  Sierra $driver */
	private Sierra $driver;

	public function __construct(Sierra $driver) {
		$this->driver = $driver;
	}

	protected function _getHoldResult($holdResultPage) : array {
		$hold_result = [];
		//Get rid of header and footer information and just get the main content
		$matches = [];

		$numMatches = preg_match('/<td.*?class="pageMainArea">(.*)?<\/td>/s', $holdResultPage, $matches);
		//For Encore theme, try with some divs
		if ($numMatches == 0) {
			$numMatches = preg_match('/<div class="requestResult">(.*?)<\/div>/s', $holdResultPage, $matches);
			if ($numMatches == 0) {
				$numMatches = preg_match('/<div class="srchhelpText">(.*?)<\/div>/s', $holdResultPage, $matches);
			}
		}
		$itemMatches = preg_match('/Choose one item from the list below/', $holdResultPage);

		if ($numMatches > 0 && $itemMatches == 0) {
			//$logger->log('Place Hold Body Text\n' . $matches[1], Logger::LOG_NOTICE);
			$cleanResponse = preg_replace("^\n|\r|&nbsp;^", "", $matches[1]);
			$cleanResponse = preg_replace("^<br\s*/>^", "\n", $cleanResponse);
			$cleanResponse = trim(strip_tags($cleanResponse));

			if (strpos($cleanResponse, "\n") > 0) {
				[
					$book,
					$reason,
				] = explode("\n", $cleanResponse);
			} else {
				$book = $cleanResponse;
				$reason = '';
			}

			$hold_result['title'] = $book;
			if (str_contains($cleanResponse, 'success') && preg_match('/request denied/', $cleanResponse) == 0) {
				//Hold was successful
				$hold_result['success'] = true;
				if (!isset($reason) || strlen($reason) == 0) {
					$hold_result['message'] = translate([
						'text' => "Your hold was placed successfully.  It may take up to 45 secona minute for the hold to appear on your account.",
						'isPublicFacing' => true,
					]);
					$hold_result['api']['title'] = translate([
						'text' => 'Hold placed successfully',
						'isPublicFacing' => true,
					]);
					$hold_result['api']['message'] = translate([
						'text' => 'Your hold was placed successfully.  It may take up to a minute for the hold to appear on your account.',
						'isPublicFacing' => true,
					]);
					$hold_result['api']['action'] = translate([
						'text' => 'Go to Holds',
						'isPublicFacing' => true,
					]);
				} else {
					$hold_result['message'] = $reason;
					$hold_result['api']['title'] = translate([
						'text' => 'Unable to place hold',
						'isPublicFacing' => true,
					]);
					$hold_result['api']['message'] = $reason;
				}
			} elseif (!isset($reason) || strlen($reason) == 0) {
				//Didn't get a reason back.  This really shouldn't happen.
				$hold_result['success'] = false;
				$hold_result['message'] = 'Did not receive a response from the circulation system.  Please try again in a few minutes.';
				$hold_result['api']['title'] = translate([
					'text' => 'Unable to place hold',
					'isPublicFacing' => true,
				]);
				$hold_result['api']['message'] = translate([
					'text' => 'Did not receive a response from the circulation system.  Please try again in a few minutes.',
					'isPublicFacing' => true,
				]);
			} else {
				//Got an error message back.
				$hold_result['success'] = false;
				$hold_result['message'] = $reason;
				$hold_result['api']['title'] = translate([
					'text' => 'Unable to place hold',
					'isPublicFacing' => true,
				]);
				$hold_result['api']['message'] = $reason;
			}
		} else {
			$hold_result['api']['title'] = translate([
				'text' => 'Unable to place hold',
				'isPublicFacing' => true,
			]);
			if ($itemMatches > 0) {
				//Get information about the items that are available for holds
				preg_match_all('/<tr\\s+class="bibItemsEntry">.*?<input type="radio" name="radio" value="(.*?)".*?>.*?<td.*?>(.*?)<\/td>.*?<td.*?>(.*?)<\/td>.*?<td.*?>(.*?)<\/td>.*?<\/tr>/s', $holdResultPage, $itemInfo, PREG_PATTERN_ORDER);
				$items = [];
				for ($i = 0; $i < count($itemInfo[0]); $i++) {
					$items[] = [
						'itemNumber' => $itemInfo[1][$i],
						'location' => trim(str_replace('&nbsp;', '', $itemInfo[2][$i])),
						'callNumber' => trim(str_replace('&nbsp;', '', $itemInfo[3][$i])),
						'status' => trim(str_replace('&nbsp;', '', $itemInfo[4][$i])),
					];
				}
				$hold_result['items'] = $items;
				if (count($items) > 0) {
					$message = 'This title requires item level holds, please select an item to place a hold on.';
					$hold_result['api']['title'] = translate([
						'text' => 'Select an Item',
						'isPublicFacing' => true,
					]);
				} else {
					$message = 'There are no holdable items for this title.';
				}
			} else {
				$message = 'Unable to contact the circulation system.  Please try again in a few minutes.';
			}
			$hold_result['success'] = false;
			$hold_result['message'] = $message;
			$hold_result['api']['message'] = $message;

			global $logger;
			$logger->log('Place Hold Full HTML\n' . $holdResultPage, Logger::LOG_NOTICE);
		}
		return $hold_result;
	}

	/**
	 * Place Volume Hold
	 *
	 * This is responsible for both volume level holds using screen scraping.
	 */
	function placeVolumeHold(User $patron, string $recordId, string $volumeId, string $pickupBranch) : array {
		global $logger;

		if (strpos($recordId, ':')) {
			$recordComponents = explode(':', $recordId);
			$recordId = $recordComponents[1];
		}

		$bib1 = $recordId;
		if (!str_starts_with($bib1, '.')) {
			$bib1 = '.' . $bib1;
		}

		$bib = substr(str_replace('.b', 'b', $bib1), 0, -1);
		if (strlen($bib) == 0) {
			return [
				'success' => false,
				'message' => 'A valid record id was not provided. Please try again.',
			];
		}

		//Get the title of the book.
		// Retrieve Full Marc Record
		require_once ROOT_DIR . '/RecordDrivers/RecordDriverFactory.php';
		$record = RecordDriverFactory::initRecordDriverById($this->driver->accountProfile->recordSource . ':' . $bib1);
		if (!$record) {
			$logger->log('Place Hold: Failed to get Marc Record', Logger::LOG_NOTICE);
			$title = null;
		} else {
			$title = $record->getTitle();
		}

		if (!empty($_REQUEST['cancelDate'])) {
			$date = $_REQUEST['cancelDate'];
		} else {
			global $library;
			if ($library->defaultNotNeededAfterDays == 0) {
				//Default to a date 6 months (half a year) in the future.
				$sixMonthsFromNow = time() + 182.5 * 24 * 60 * 60;
				$date = date('m/d/Y', $sixMonthsFromNow);
			} else {
				//Default to a date 6 months (half a year) in the future.
				$nnaDate = time() + $library->defaultNotNeededAfterDays * 24 * 60 * 60;
				$date = date('m/d/Y', $nnaDate);
			}
		}

		[
			$Month,
			$Day,
			$Year,
		] = explode("/", $date);

		//Make sure to connect via the driver so cookies will be correct
		$this->driver->curlWrapper->curl_connect();

//			curl_setopt($curl_connection, CURLOPT_POST, true);

		/** @noinspection PhpUnusedLocalVariableInspection */
		$loginResult = $this->driver->_curl_login($patron);

		$volumeId = substr(str_replace('.j', 'j', $volumeId), 0, -1);
		$curl_url = $this->driver->getVendorOpacUrl() . "/search/.$bib/.$bib/1,1,1,B/request~$bib&jrecnum=$volumeId";

		global $librarySingleton;
		$patronHomeBranch = $librarySingleton->getPatronHomeLibrary($patron);
		if ($patronHomeBranch->defaultNotNeededAfterDays != -1) {
			$post_data['needby_Month'] = $Month;
			$post_data['needby_Day'] = $Day;
			$post_data['needby_Year'] = $Year;
		} else {
			$post_data['needby_Month'] = 'Month';
			$post_data['needby_Day'] = 'Day';
			$post_data['needby_Year'] = 'Year';
		}

		$post_data['pat_submit'] = "submit";
		$post_data['locx00'] = str_pad($pickupBranch, 5); // padded with spaces, which will get url-encoded into plus signs by httpd_build_query() in the curlPostPage() method.
		if (!empty($itemId) && $itemId != -1) {
			$post_data['radio'] = $itemId;
		}

		$sResult = $this->driver->curlWrapper->curlPostPage($curl_url, $post_data);

		$logger->log("Placing hold $recordId : $title", Logger::LOG_NOTICE);

		$sResult = preg_replace("/<!--([^(-->)]*)-->/", "", $sResult);

		//Parse the response to get the status message
		$hold_result = $this->_getHoldResult($sResult);
		$hold_result['title'] = $title;
		$hold_result['bid'] = $bib1;

		return $hold_result;
	}
}