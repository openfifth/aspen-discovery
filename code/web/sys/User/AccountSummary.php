<?php /** @noinspection PhpMissingFieldTypeInspection */


class AccountSummary extends DataObject {
	public $__table = 'user_account_summary';
	public $id;
	public $source;
	public $userId;
	public $numCheckedOut;
	public $numCheckoutsRemaining; //Currently used for Hoopla Only
	public $numOverdue;
	public $numAvailableHolds;
	public $numUnavailableHolds;
	public $totalFines;
	public $expirationDate;
	public $lastLoaded;
	public $hasUpdatedSavedSearches;
	//This determines if the data stored with account summary is stale so we can force a reload
	public $dataIsStale;
	public $holdsAreStale;
	public $checkoutsAreStale;

	protected $_materialsRequests;
	protected $_readingHistory;
	protected $_numUpdatedSearches;

	public function getNumericColumnNames(): array {
		return [
			'userId',
			'numCheckedOut',
			'numCheckoutsRemaining',
			'numOverdue',
			'numAvailableHolds',
			'numUnavailableHolds',
			'totalFines',
			'expirationDate',
			'lastLoaded',
			'hasUpdatedSavedSearches',
			'dataIsStale',
			'holdsAreStale',
			'checkoutsAreStale',
		];
	}

	function objectHistoryEnabled() : bool {
		return false;
	}

	/**
	 * @return int
	 */
	public function getMaterialsRequests() : int {
		return $this->_materialsRequests;
	}

	/**
	 * @param int $materialsRequests
	 */
	public function setMaterialsRequests(int $materialsRequests): void {
		$this->_materialsRequests = $materialsRequests;
	}

	public function getNumHolds() : int {
		return $this->numAvailableHolds + $this->numUnavailableHolds;
	}

	/**
	 * @return int
	 */
	public function getReadingHistory() : int {
		return $this->_readingHistory;
	}

	/**
	 * @param int $readingHistory
	 */
	public function setReadingHistory(int $readingHistory): void {
		$this->_readingHistory = $readingHistory;
	}

	public function setNumUpdatedSearches(int $numUpdatedSearches): void {
		$this->_numUpdatedSearches = $numUpdatedSearches;
	}

	private $_expired = null;
	private $_expireClose = null;

	private function loadExpirationInfo() : void {
		if ($this->expirationDate > 0) {
			$timeNow = time();
			$this->_expired = 0;
			$timeToExpire = $this->expirationDate - $timeNow;
			if ($timeToExpire <= 30 * 24 * 60 * 60) {
				if ($timeToExpire <= 0) {
					$this->_expired = 1;
				}
				$this->_expireClose = 1;
			} else {
				$this->_expireClose = 0;
			}
		} else {
			$this->_expired = 0;
			$this->_expireClose = 0;
		}
	}

	public function isExpired() : bool {
		if ($this->_expired === null) {
			$this->loadExpirationInfo();
		}
		return $this->_expired;
	}

	public function isExpirationClose() : bool {
		if ($this->_expireClose === null) {
			$this->loadExpirationInfo();
		}
		return $this->_expireClose;
	}

	public function expiresOn() : string {
		return date('M j, Y', $this->expirationDate);
	}

	//This is set and then returned as part of the toArray method
	private $_expirationNotice = '';

	public function setExpirationNotice(string $notice) : void {
		$this->_expirationNotice = $notice;
	}

	private $_finesBadge = '';

	public function setFinesBadge(string $notice) : void {
		$this->_finesBadge = $notice;
	}

	public function toArray($includeRuntimeProperties = true, $encryptFields = false): array {
		$return = parent::toArray($includeRuntimeProperties, $encryptFields);
		$return['expires'] = date('M j, Y', $this->expirationDate);
		$return['expired'] = $this->isExpired();
		$return['expireClose'] = $this->isExpirationClose();
		$return['expirationNotice'] = $this->_expirationNotice;
		$return['numHolds'] = $this->getNumHolds();
		if ($this->_numUpdatedSearches > 0) {
			$return['savedSearches'] = translate([
				'text' => '%1% Updated',
				1 => $this->_numUpdatedSearches,
				'isPublicFacing' => true,
			]);
		} else {
			$return['savedSearches'] = '';
		}
		$return['finesBadge'] = $this->_finesBadge;
		return $return;
	}

	/**
	 * @return void
	 */
	public function resetCounters() : void {
		$this->numCheckedOut = 0;
		$this->numCheckoutsRemaining = 0;
		$this->numOverdue = 0;
		$this->numAvailableHolds = 0;
		$this->numUnavailableHolds = 0;
		$this->totalFines = 0;
		$this->expirationDate = 0;
	}

	/**
	 * Increments the number of unavailable holds after a hold is placed.
	 * @return void
	 */
	public function incrementNumberOfUnavailableHolds() : void {
		$this->__set('numUnavailableHolds', ++$this->numUnavailableHolds);
		$this->update();
	}

	public function markHoldsStale() : void {
		$this->__set('holdsAreStale', 1);
		$this->update();
	}

	public function clearHoldsStale() : void {
		$this->__set('holdsAreStale', 0);
		$this->update();
	}

	public function decrementAvailableHolds() : void {
		$this->__set('numAvailableHolds', --$this->numAvailableHolds);
		$this->update();
	}

	public function decrementUnavailableHolds() : void {
		$this->__set('numUnavailableHolds', --$this->numUnavailableHolds);
		$this->update();
	}
}