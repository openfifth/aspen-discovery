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
			'value' => [
				'property' => 'value',
				'type' => 'text',
				'label' => 'Parameter value',
				'description' => 'If the parameter value is know and can be tied to a setting, please enter it here.',
				'hideInLists' => false,
				'default' => false,
			], 
			'includeInUrl' => [
				'property' => 'includeInUrl',
				'type' => 'checkbox',
				'label' => 'Include In URL',
				'description' => 'Whether or not to this URL parameter in the HeyCentric payment URL',
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
			// Should be drop down?
			'dbTableName' => [
				'property' => 'dbTableName',
				'type' => 'text',
				'label' => 'Name of the matching ILS database table',
				'description' => 'The name of the ILS database table where the parameter value is stored',
				'hideInLists' => false,
				'default' => null,
			],
			// Should be drop down?
			'dbTableFieldName' => [
				'property' => 'dbTableFieldName',
				'type' => 'text',
				'label' => 'Name of the matching ILS database field',
				'description' => 'The name of the ILS database field where the parameter value is stored',
				'hideInLists' => false,
				'default' => null,
			],
			'isKohaAdditionalField' => [
				'property' => 'valueIsKohaAdditionalField',
				'type' => 'checkbox',
				'label' => 'Is Koha Additional Field',
				'description' => 'Whether this ILS database field in a Koha additional field',
				'hideInLists' => false,
				'default' => false,
			],
			// TODO: toggle based on isKohaAdditionalField value
			'valueIsFromKohaAdditionalField' => [
				'property' => 'valueIsFromKohaAdditionalField',
				'type' => 'enum',
				'label' => 'Name of the matching Koha additional field if any',
				'description' => 'The name of the additional field in Koha where the parameter value is stored',
				'values' => $additionaFields,
				'hideInLists' => false,
				'default' => null,
			],
		];

		return $structure;
	}
}
