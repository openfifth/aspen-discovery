<?php

require_once ROOT_DIR . '/sys/ExploreMoreSourceLibrary.php';
require_once ROOT_DIR . '/sys/ExploreMoreSourceLocation.php';

class ExploreMoreSource extends DataObject {
	public $__table = 'explore_more_source';
	public $id;
	public $source;
	public $weight;
	public $showInExploreMore;
	private $libraries = [];
	private $locations = [];

	public function setLibraries($libraries) {
		if (!is_array($libraries)) {
			if ($libraries === null || $libraries === '') {
				$libraries = [];
			} else {
				$libraries = [$libraries];
			}
		}
		// Normalize all values to strings and remove empty values
		$this->libraries = array_values(array_filter(array_map('strval', $libraries), function($v) { return $v !== '' && $v !== null; }));
	}

	public function setLocations($locations) {
		$this->locations = $locations;
	}

	/** @var ExploreMoreSource[] */
	protected $_sources;

	public function getSources() {
		if (!isset($this->_sources) && $this->id) {
			$this->_sources = [];
			$sourceObj = new ExploreMoreSource();
			$sourceObj->orderBy('weight ASC');
			$sourceObj->find();
			while ($sourceObj->fetch()) {
				$this->_sources[$sourceObj->id] = clone($sourceObj);
			}
		}
		return $this->_sources;
	}

	public function getLibraries() {
		   if (!is_array($this->libraries) || empty($this->libraries)) {
			   $this->libraries = [];
			   if ($this->id) {
				   $obj = new ExploreMoreSourceLibrary();
				   $obj->exploreMoreSourceId = $this->id;
				   $obj->find();
				   while ($obj->fetch()) {
					   $this->libraries[] = (string)$obj->libraryId;
				   }
			   }
		   }
		   // Normalize and reindex
		   $this->libraries = array_values(array_map('strval', $this->libraries));
		   return $this->libraries;
	}

	public function getLocations() {
		if (!is_array($this->locations) || empty($this->locations)) {
			$this->locations = [];
			if ($this->id) {
				$obj = new ExploreMoreSourceLocation();
				$obj->exploreMoreSourceId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->locations[] = (string)$obj->locationId;
				}
			}
		}
		// Normalize and reindex
		$this->locations = array_values(array_map('strval', $this->locations));
		return $this->locations;
	}

	public function insert(string $context = '') : int|bool {
		$result = parent::insert($context);
		$this->saveLibraries();
		$this->saveLocations();
		return $result;
	}

	public function update(string $context = '') : int|bool {
		$result = parent::update($context);
		$this->saveLibraries();
		$this->saveLocations();
		return $result;
	}

	private function saveLibraries() {
		if ($this->id !== null) {
			$obj = new ExploreMoreSourceLibrary();
			$obj->exploreMoreSourceId = $this->id;
			$obj->delete(true);
			if (is_array($this->libraries)) {
				foreach ($this->libraries as $libraryId) {
					$lib = new ExploreMoreSourceLibrary();
					$lib->exploreMoreSourceId = $this->id;
					$lib->libraryId = (string)$libraryId;
					$lib->insert();
				}
			}
		}
	}

	private function saveLocations() {
		if ($this->id !== null) {
			$obj = new ExploreMoreSourceLocation();
			$obj->exploreMoreSourceId = $this->id;
			$obj->delete(true);
			if (is_array($this->locations)) {
				foreach ($this->locations as $locationId) {
					$loc = new ExploreMoreSourceLocation();
					$loc->exploreMoreSourceId = $this->id;
					$loc->locationId = $locationId;
					$loc->insert();
				}
			}
		}
	}

	public function __get($name) {
		if ($name === 'libraries') {
			return $this->getLibraries();
		} elseif ($name === 'locations') {
			return $this->getLocations();
		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if ($name === 'libraries') {
			$debug = true;
			$this->setLibraries($value);
		} elseif ($name === 'locations') {
			$this->setLocations($value);
		} else {
			parent::__set($name, $value);
		}
	}

	static function getObjectStructure($context = ''): array {
		require_once ROOT_DIR . '/sys/ExploreMoreSourceEntry.php';
		$entryStructure = ExploreMoreSourceEntry::getObjectStructure($context);
		unset($entryStructure['exploreMoreSourceGroupId']);
		unset($entryStructure['weight']);
		// Ensure keys are strings for UI matching
		$libraryList = Library::getLibraryList(false);
		$libraryListStringKeys = [];
		foreach ($libraryList as $k => $v) {
			$libraryListStringKeys[(string)$k] = $v;
		}
		$locationList = Location::getLocationList(false);
		$locationListStringKeys = [];
		foreach ($locationList as $k => $v) {
			$locationListStringKeys[(string)$k] = $v;
		}
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'ID',
				'description' => 'The unique id',
				'hideInLists' => true,
			],
			'source' => [
				'property' => 'source',
				'type' => 'enum',
				'label' => 'Source',
				'values' => [
					'Catalog' => 'Catalog',
					'EBSCO EDS' => 'EBSCO EDS',
					'EBSCOhost' => 'EBSCOhost',
					'Summon' => 'Summon',
					'Gale' => 'Gale',
					'CloudSource' => 'CloudSource',
					'Events' => 'Events',
					'Web Indexer' => 'Web Indexer',
					'Lists' => 'Lists',
					'Open Archives' => 'Open Archives',
					'Series' => 'Series',
					'Genealogy' => 'Genealogy',
				],
				'required' => true,
				'readOnly' => true,
			],
			'showInExploreMore' => [
				'property' => 'showInExploreMore',
				'type' => 'checkbox',
				'label' => 'Show in Explore More',
				'description' => 'Whether this source displays content in Explore More',
				'default' => '1',
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'label' => 'Libraries',
				'description' => 'Libraries where this source is visible',
				'listStyle' => 'checkboxSimple',
				'values' => $libraryListStringKeys,
				'uiOnly' => true,
			],
			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'label' => 'Locations',
				'description' => 'Locations where this source is visible',
				'listStyle' => 'checkboxSimple',
				'values' => $locationListStringKeys,
				'uiOnly' => true,
			],
		];
		return $structure;
	}
}
