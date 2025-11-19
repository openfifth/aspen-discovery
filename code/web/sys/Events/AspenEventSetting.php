<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/LibraryEventsSetting.php';
require_once ROOT_DIR . '/sys/Events/EventsBranchMapping.php';

/**
 * Settings for Aspen Events (Registration)
 */
class AspenEventSetting extends DataObject {
	public $__table = 'aspen_event_settings';
	public $id;
	public $name;
	public $registrationModalBody;
	public $runFullUpdate;
	/** @noinspection PhpUnused */
	public $numberOfDaysToIndex;

	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		/** @noinspection HtmlRequiredAltAttribute */
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
			],
			'indexingSection' => [
				'property' => 'indexingSection',
				'type' => 'section',
				'label' => 'Indexing Settings',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'runFullUpdate' => [
						'property' => 'runFullUpdate',
						'type' => 'checkbox',
						'label' => 'Run Full Update',
						'description' => 'Whether or not a full update of all records should be done on the next pass of indexing',
						'default' => 0,
					],
					'numberOfDaysToIndex' => [
						'property' => 'numberOfDaysToIndex',
						'type' => 'integer',
						'label' => 'Number of Days to Index',
						'description' => 'How many days in the future to index events',
						'default' => 365,
					],
					'lastUpdateOfAllEvents' => [
						'property' => 'lastUpdateOfAllEvents',
						'type' => 'timestamp',
						'label' => 'Last Update Of All Events',
						'readOnly' => 1,
					],
					'lastUpdateOfChangedEvents' => [
						'property' => 'lastUpdateOfChangedEvents',
						'type' => 'timestamp',
						'label' => 'Last Update Of Changed Events',
						'readOnly' => 1,
					],
				]
			],
			'registrationSection' => [
				'property' => 'registrationSection',
				'type' => 'section',
				'label' => 'Registration Settings',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'registrationModalBody' => [
						'property' => 'registrationModalBody',
						'type' => 'html',
						'label' => 'Registration Modal Body',
						'description' => 'The body of the modal for event registration information',
						'allowableTags' => '<p><em><i><strong><b><a><ul><ol><li><h1><h2><h3><h4><h5><h6><h7><pre><code><hr><table><tbody><tr><th><td><caption><img><br><div><span><sub><sup>',
						'hideInLists' => true,
					],
				]
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	public function getRegistrationModalBody(): string {
		if (!$this->registrationModalBody || $this->registrationModalBody == ""){
			global $interface;
			return $interface->fetch('AspenEvents/registrationModalBody.tpl');
		} 
		return $this->registrationModalBody;
	}
}