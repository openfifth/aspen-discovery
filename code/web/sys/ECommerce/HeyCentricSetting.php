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
		];

		if (!UserAccount::userHasPermission('Library eCommerce Options')) {
			unset($structure['libraries']);
		}
		return $structure;
	}
}