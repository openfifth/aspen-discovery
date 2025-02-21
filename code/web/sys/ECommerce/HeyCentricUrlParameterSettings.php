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
		// TODO: handle other ILSs (add a 'hasAdditionalFields' methods to catalog drivers OR check it the catalog driver's name is Koha)
		global $library;
		$accountProfile = $library->getAccountProfile();
		$catalogDriverName = trim($accountProfile->driver);
		$catalogDriver = CatalogFactory::getCatalogConnectionInstance($catalogDriverName, $accountProfile);
		$additionaFields = $catalogDriver->getAdditionalFieldsNamesByTable('account_debit_types');
		array_unshift($additionaFields, '');
		
		$structure = [
			'includeInUrl' => [
				'property' => 'includeInUrl',
				'type' => 'checkbox',
				'label' => 'Include In URL',
				'description' => 'Whether or not to this URL parameter in the HeyCentric payment URL',
				'hideInLists' => false,
			],  
			'includeInHash' => [
				'property' => 'includeInHash',
				'type' => 'checkbox',
				'label' => 'Include In Hash',
				'description' => 'Whether or not to include this URL parameter in the HeyCentric payment URL hash',
				'hideInLists' => false,
			],
			'valueIsFromKohaAddionalField' => [
				'property' => 'valueIsFromKohaAddionalField',
				'type' => 'enum',
				'label' => 'Name of the matching Koha additional field if any',
				'description' => 'Whether or not the value for this parameter is stored in an additional field',
				'values' => $additionaFields,
				'hideInLists' => false,
			],
		];

		return $structure;
	}
}
