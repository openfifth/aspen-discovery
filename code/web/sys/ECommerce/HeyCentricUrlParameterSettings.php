<?php
require_once ROOT_DIR . '/sys/ECommerce/HeyCentricUrlParameters.php'; 

class HeyCentricUrlParameterSettings extends DataObject {
	public $__table = 'heycentric_url_parameter_settings';
	public $id;
	public $heyCentricSettingId;
	public $heyCenticUrlParameterId;
	public $includeInUrl;
	public $includeInHash;
	public $valueIsFromKohaAddionalField;

	static function getObjectStructure($context = ''): array {
		// TODO: Error handling
		global $library;
		$accountProfile = $library->getAccountProfile();
		$catalogDriverName = trim($accountProfile->driver);
		$catalogDriver = CatalogFactory::getCatalogConnectionInstance($catalogDriverName, $accountProfile);
		$additionalFields = $catalogDriver->hasAdditionalFields() ? $catalogDriver->getAdditionalFieldNames(null, null) : null;
		array_unshift($additionalFields, null);
		
		$structure = [
			'value' => [
				'property' => 'value',
				'type' => 'text',
				'label' => 'Parameter value',
				'description' => 'If the parameter value is known and can be tied to a setting, please enter it here.',
				'hideInLists' => false,
				'default' => false,
			], 
			'includeInUrl' => [
				'property' => 'includeInUrl',
				'type' => 'checkbox',
				'label' => 'Include In URL',
				'description' => 'Whether or not to to include this URL parameter in the HeyCentric payment URL',
				'hideInLists' => false,
				'default' => false,
			],  
			'includeInHash' => [
				'property' => 'includeInHash',
				'type' => 'checkbox',
				'label' => 'Include In Hash',
				'description' => 'Whether or not to include this URL parameter in the HeyCentric payment URL hash',
				'hideInLists' => false,
				'default' => false,
			],
			'kohaAdditionalFieldName' => [
				'property' => 'kohaAdditionalFieldName',
				'type' => 'enum',
				'label' => 'Name of the matching Koha additional field if any',
				'description' => 'If the value to be used for this parameter is stored in a Koha additional field, select its name here. Otherwise, please leave this blank.',
				'values' => $additionalFields,
				'hideInLists' => false,
				'default' => null,
			],
		];

		if (!$additionalFields) {
			unset($structure['kohaAdditionalFieldName']);
		}

		return $structure;
	}
}
