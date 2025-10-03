<?php /** @noinspection PhpMissingFieldTypeInspection */


class IlsVolumeInfo extends DataObject {
	public $__table = 'ils_volume_info';    // table name
	public $id;
	public $recordId;
	public $displayLabel;
	public $relatedItems;
	public $volumeId;
	public $displayOrder;

	public $_hasLocalItems;
	public $_needsIllRequest;
	public $_allItems = [];
	public $_editions = [];

	public function setHasLocalItems(bool $hasLocalItems) : void {
		$this->_hasLocalItems = $hasLocalItems;
	}

	public function hasLocalItems(): bool {
		return (bool)$this->_hasLocalItems;
	}

	public function setEdition($edition, $data): void {
		$this->_editions[$edition] = $data;
		$this->_editions[$edition]->label = translate([
			'text' => 'Select This Edition',
			'isPublicFacing' => true,
		]);
	}

	public function getEditions(): bool {
		return $this->_editions;
	}

	public function setEditionStatus($edition, $status): void {
		$this->_editions[$edition]->statusIndicator = $status;
	}

	public function setEditionCover($edition, $cover): void {
		$this->_editions[$edition]->coverUrl = $cover;
	}

	public function setNeedsIllRequest(bool $needsIllRequest) : void {
		$this->_needsIllRequest = $needsIllRequest;
	}

	public function needsIllRequest(): bool {
		return (bool)$this->_needsIllRequest;
	}

	public function addItem($item) : void {
		$this->_allItems[] = $item;
	}

	public function getItems() {
		return $this->_allItems;
	}

	private static $preloadedVolumes = [];

	/**
	 * Preloads volumes for a type and identifier using minimal database queries
	 *
	 * @param string $type
	 * @param array $identifiers
	 * @return void
	 */
	static function preloadVolumeInfo(string $type, array $identifiers) : void {
		global $indexingProfiles;
		//Volumes are only for ILS records
		if (!in_array($type, $indexingProfiles)) {
			return;
		}
		if (!isset(self::$preloadedVolumes[$type])) {
			self::$preloadedVolumes[$type] = [];
		}
		foreach ($identifiers as $identifier) {
			if (!array_key_exists($identifier, self::$preloadedVolumes[$type])) {
				self::$preloadedVolumes[$type][$identifier] = [];
			}
		}
		$volumeInfo = new IlsVolumeInfo();
		$volumeInfo->whereAddIn('recordId', $identifiers, true);
		$volumeInfo->orderBy('displayOrder ASC, displayLabel ASC');
		$allRelatedVolumes = $volumeInfo->fetchAll();
		foreach ($allRelatedVolumes as $relatedVolume) {
			self::$preloadedVolumes[$type][$relatedVolume->recordId][] = $relatedVolume;
		}
	}

	/**
	 * @param string $type
	 * @param string $identifier
	 * @return IlsVolumeInfo[]
	 */
	static function getVolumesForRecord(string $type, string $identifier) : array {
		global $indexingProfiles;
		//Hold Summaries are only for ILS records
		if (!in_array($type, $indexingProfiles)) {
			return [];
		}
		if (isset(self::$preloadedVolumes[$type]) && array_key_exists($identifier, self::$preloadedVolumes[$type])) {
			return self::$preloadedVolumes[$type][$identifier];
		}else{
			$volumeInfo = new IlsVolumeInfo();
			$volumeInfo->recordId = $identifier;
			$volumeInfo->orderBy('displayOrder ASC, displayLabel ASC');
			return $volumeInfo->fetchAll();
		}
	}
}