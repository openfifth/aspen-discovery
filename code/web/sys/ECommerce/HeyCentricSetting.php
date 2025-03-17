<?php

require_once ROOT_DIR . '/sys/ECommerce/HeyCentricUrlParameterSetting.php'; 
require_once ROOT_DIR . '/sys/ECommerce/HeyCentricUrlParameter.php'; 

class HeyCentricSetting extends DataObject {
	public $__table = 'heycentric_setting';
	public $id;
	public $name;
	public $baseUrl;
	public $privateKey;

	private $_libraries;
	private $_locations;
	private $_urlParameterSettings;

	public function getEncryptedFieldNames(): array {
		return ['privateKey'];
	}

	static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Locations'));
		$urlParameterSettingFields = HeyCentricUrlParameterSetting::getObjectStructure();

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
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use these settings',
				'values' => $libraryList,
				'hideInLists' => true,
			],
		];

		if (!UserAccount::userHasPermission('Library eCommerce Options')) {
			unset($structure['libraries']);
		}
		return $structure;
	}

	public function __get($name): array|null {
		if ($name == "libraries" && !isset($this->_libraries) && $this->id) {
			$this->_libraries = [];
			$obj = new Library();
			$obj->heyCentricSettingId = $this->id;
			$obj->find();
			while ($obj->fetch()) {
				$this->_libraries[$obj->libraryId] = $obj->libraryId;
			}
			return $this->_libraries;
		}

		return parent::__get($name);
	}
	public function __set($name, $value): void {

		switch ($name) {
			case "libraries":
				$this->_libraries = $value;
				break;
			default:
				parent::__set($name, $value);
		}
	}

	public function update($context = ''): bool {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return true;
	}

	public function insert($context = ''): int|bool {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	public function saveLibraries(): void {
		if (!isset ($this->_libraries) || !is_array($this->_libraries)) {
			return;
		}
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		foreach ($libraryList as $libraryId => $displayName) {
			$library = new Library();
			$library->libraryId = $libraryId;
			$library->find(true);
			if (in_array($libraryId, $this->_libraries)) {
				if ($library->heyCentricSettingId != $this->id) {
					$library->finePaymentType = 16;
					$library->heyCentricSettingId = $this->id;
					$library->update();
				}
			} else {
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