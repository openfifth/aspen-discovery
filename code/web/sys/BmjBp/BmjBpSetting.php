<?php

class BmjBpSetting extends DataObject {
	public $__table = 'bmj_bp_settings';
	public $id;
	public $name;
	public $bmjBpBaseApiUrl;
	public $bmjBpApiKey;
	public $bmjBpApiSecret;

	private $_libraries;

	function getEncryptedFieldNames(): array {
		return ['bmjBpApiKey', 'bmjBpApiSecret'];
	}

	public static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));

		return [
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
				'maxLength' => 50,
				'description' => 'A name for these settings',
				'required' => true,
			],
			'bmjBpBaseApiUrl' => [
				'property' => 'bmjBpBaseApiUrl',
				'type' => 'text',
				'label' => 'BMJ Best Practice Base API URL',
				'description' => 'The url to use when connecting to BMJ Best Practice API',
				'hideInLists' => true,
			],
			'bmjBpApiKey' => [
				'property' => 'bmjBpApiKey',
				'type' => 'storedPassword',
				'label' => 'BMJ Best Practice API Key',
				'description' => 'The key to use when connecting to the BMJ Best Practice API',
				'hideInLists' => true,
			],
			'bmjBpApiSecret' => [
				'property' => 'bmjBpApiSecret',
				'type' => 'storedPassword',
				'label' => 'BMJ Best Practice API Secret',
				'description' => 'The secret to use when connecting to the BMJ Best Practice API',
				'hideInLists' => true,
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use this setting',
				'values' => $libraryList,
				'hideInLists' => true,
			],
		];
	}

	public function __get($name) {
		if ($name == "libraries") {
			if (!isset($this->_libraries) && $this->id) {
				$this->_libraries = [];
				$obj = new Library();
				$obj->bmjBpSettingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_libraries[$obj->libraryId] = $obj->libraryId;
				}
			}
			return $this->_libraries;
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
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
					if ($library->bmjBpSettingId != $this->id) {
						$library->finePaymentType = 2;
						$library->bmjBpSettingId = $this->id;
						$library->update();
					}
				} else {
					if ($library->bmjBpSettingId == $this->id) {
						if ($library->finePaymentType == 2) {
							$library->finePaymentType = 0;
						}
						$library->bmjBpSettingId = -1;
						$library->update();
					}
				}
			}
			unset($this->_libraries);
		}
	}
}