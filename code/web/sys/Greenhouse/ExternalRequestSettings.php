<?php /** @noinspection PhpMissingFieldTypeInspection */


class ExternalRequestSettings extends DataObject {
	public $__table = 'external_request_settings'; //TODO make this table
	public $id;
	public $requestType;

	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		//TODO idea: table of settings for external request type
		// modify externalRequestLogEntry::getForceDebuggingLogStatus to check this table
		// and if the setting is enabled return true.
		// will also need to modify the template to link to the page for the setting
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'RequestType' => [
				'property' => 'requestType',
				'type' => 'text',
				'label' => 'Request Type',
				'description' => 'Request Type to match requests against. Could be just api or api.method'

			],
			'enabled' => [
				'property' => 'enabled',
				'type' => 'checkbox',
				'label' => 'Enabled?',
				'description' => 'Whether or not to always log requests when they start with the given type',
				'default' => '0',
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}
}