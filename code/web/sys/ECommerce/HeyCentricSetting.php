<?php

require_once ROOT_DIR . '/sys/ECommerce/HeyCentricUrlParameterSettings.php'; 

class HeyCentricSetting extends DataObject {
	public $__table = 'heycentric_setting';
	public $id;
	public $name;
	public $showPayLater;
	public $baseUrl;
	public $client;
	public $privateKey;
	public $area;
	public $till;
	public $entity;
	public $rurl;

	private $_libraries;
	private $_locations;
	private $_urlParameters;

	static function getHeyCentricUrlParamFields() {
		$urlParamsArr = [];
		$urlParam = new HeyCentricUrlParameter();
		$urlParam = $urlParam->fetchAll();
		
		foreach($urlParam as $param) {
			$urlParamsArr[] = [
				'id' => $param->id,
				'property' => $param->name,
				'type' => 'section',
				'label' => $param->name,
				'description' => '',
				'maxLength' => 10,
				'required' => true,
				'properties' => HeyCentricUrlParameterSettings::getObjectStructure(),
			];
		}
		return $urlParamsArr;
	}

	public function getEncryptedFieldNames(): array {
		return ['privateKey'];
	}

	static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Locations'));
		$urlParams = HeyCentricSetting::getHeyCentricUrlParamFields();

		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'A name for the settings',
				'maxLength' => 50,
			],
			'baseUrl' => [
				'property' => 'baseUrl',
				'type' => 'text',
				'hideInLists' => true,
				'label' => 'HeyCentric base URL',
				'description' => 'The base URL that links to the HeyCentric platform where patrons can make payments',
				'maxLength' => 50,
				'required' => true,
			],
			'privateKey' => [
				'property' => 'privateKey',
				'hideInLists' => true,
				'type' => 'storedPassword',
				'label' => 'HeyCentric Private Key',
				'description' => 'The HeyCentric Private Key for your site',
				'maxLength' => 50,
			],
			'urlParameters' => [
				'property' => 'urlParameters',
				'type' => 'section',
				'hideInLists' => true,
				'label' => 'HeyCentric Payment URL Parameters',
				'description' => 'The parameters to include when forming the HeyCentric payment URL and/or its hash',
				'maxLength' => 50,
				'required' => true,
				'properties' => $urlParams,
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use these settings',
				'values' => $libraryList,
				'hideInLists' => true,
			],
			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Locations',
				'description' => 'Define locations that use these settings',
				'values' => $locationList,
				'hideInLists' => true,
			],
		];

		if (!UserAccount::userHasPermission('Library eCommerce Options')) {
			unset($structure['libraries']);
		}
		return $structure;
	}

	public function __get($name) {
		if ($name == "libraries") {
			if (!isset($this->_libraries) && $this->id) {
				$this->_libraries = [];
				$obj = new Library();
				$obj->heyCentricSettingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_libraries[$obj->libraryId] = $obj->libraryId;
				}
			}
			return $this->_libraries;
		} else {

			// TODO: handle locations
			// TODO: handle url parameters and parameters settings
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		switch ($name) {
			case "libraries":
				$this->_libraries = $value;
				break;
			case "locations":
				$this->_locations = $value;
				break;
			case "urlParameters":
				$this->_urlParameters = $value;
				break;
			default:
				parent::__set($name, $value);
		}
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
			$this->saveUrlParameters();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
			$this->saveUrlParameters();
		}
		return $ret;
	}

	public function saveLibraries() {
		if (isset ($this->_libraries) && is_array($this->_libraries)) {
			$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
			foreach ($libraryList as $libraryId => $displayName) {
				$library = new Library();
				$library->libraryId = $libraryId;
				$library->find(true);
				if (in_array($libraryId, $this->_libraries)) {
					//We want to apply the scope to this library
					if ($library->heyCentricSettingId != $this->id) {
						$library->finePaymentType = 16;
						$library->heyCentricSettingId = $this->id;
						$library->update();
					}
				} else {
					//It should not be applied to this scope. Only change if it was applied to the scope
					if ($library->heyCentricSettingId == $this->id) {
						if ($library->finePaymentType == 16) {
							$library->finePaymentType = 0;
						}
						$library->heyCentricSettingId = -1;
						$library->update();
					}
				}
			}
			unset($this->_libraries);
		}
	}

	public function saveUrlParameters() {
		// TODO: saveURLParameters
		if (isset ($this->_urlParameters) && is_array($this->_urlParameters)) {
			$urlParams = HeyCentricSetting::getHeyCentricUrlParamFields();
			foreach ($urlParams as $urlParam) {
				$urlParamSetting = new HeyCentricUrlParameterSettings();
				$urlParamSetting->heyCentricSettingId = $this->id;
				$urlParamSetting->heyCenticUrlParameterId = $urlParam['id'];
				$urlParamSetting->update();
			}
			global $logger;
			$logger->log(json_encode($urlParamSetting), Logger::LOG_ERROR);
			unset($this->_urlParameters);
		}
	}


 	// TODO: make HeyCentric specifc instead of websites
	public function getLocations() {
		if (!isset($this->_locations) && $this->id) {
			$this->_locations = [];
			$location = new LocationWebsiteIndexing();
			$location->settingId = $this->id;
			$location->find();
			while ($location->fetch()) {
				$this->_locations[$location->locationId] = $location->locationId;
			}
		}
		return $this->_locations;
	}

	public function saveLocations() {
		if (isset($this->_locations) && is_array($this->_locations)) {

			foreach ($this->_locations as $libraryId) {
				$locationWebsiteIndexing = new LocationWebsiteIndexing();

				$locationWebsiteIndexing->settingId = $this->id;
				$locationWebsiteIndexing->locationId = $libraryId;
				$locationWebsiteIndexing->insert();
			}
			unset($this->_locations);
		}
	}
}