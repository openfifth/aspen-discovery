<?php
require_once ROOT_DIR . '/sys/ECommerce/HeyCentricUrlParameter.php'; 

class HeyCentricUrlParameterSetting extends DataObject {
	public $__table = 'heycentric_url_parameter_setting';
	public $id;
	public $value;
	public $heyCentricSettingId;
	public $heyCentricUrlParameterId;
	public $includeInUrl;
	public $includeInHash;
	public $kohaAdditionalField;

	static function getObjectStructure($context = ''): array {
		global $library;
		$accountProfile = $library->getAccountProfile();
		$catalogDriverName = trim($accountProfile->driver);
		$catalogDriver = CatalogFactory::getCatalogConnectionInstance($catalogDriverName, $accountProfile);
		$additionalFineFields = $catalogDriver->hasAdditionalFineFields() ? $catalogDriver->getAdditionalFieldNames('accountlines', null) : null;
		$additionalDebitTypeFields = $catalogDriver->hasAdditionalFineFields() ? $catalogDriver->getAdditionalFieldNames('account_debit_types', null) : null;
		$additionalLibraryBranchFields = $catalogDriver->hasAdditionalFineFields() ? $catalogDriver->getAdditionalFieldNames('branches', null) : null;
		
		$urlParam = new HeyCentricUrlParameter();
		$urlParam = $urlParam->fetchAll();
		
		$structure = [];
		
		foreach ($urlParam as $param) {
			$additionalFieldsWithDefault = ['none' => 'none'] + $additionalLibraryBranchFields; // Also applies to URL parameter that are not multiline and are tied to the general payment itself rather than to a specific line on that payment
			$additionalFieldsWithDefault += $param->multiline ? $additionalFineFields + $additionalDebitTypeFields : []; // Only multiline URL parameters are tied to a specific fine (Koha accountline) and have the required unique identifier.
			$propertyStructure = [
				'id' => $param->id,
				'property' => $param->name,
				'type' => 'section',
				'label' => $param->name,
				'maxLength' => 10,
				'properties' => [ 
					$param->name . '_includeInUrl' => [
						'property' => $param->name . '_includeInUrl',
						'type' => 'checkboxFromNestedSection',
						'label' => 'Include In URL',
						'description' => 'Whether or not to to include this URL parameter in the HeyCentric payment URL.',
						'hideInLists' => false,
						'default' => false,
					],  
					$param->name . '_includeInHash' => [
						'property' => $param->name . '_includeInHash',
						'type' => 'checkboxFromNestedSection',
						'label' => 'Include In Hash',
						'description' => 'Whether or not to include this URL parameter in the HeyCentric payment URL hash.',
						'hideInLists' => false,
						'default' => false,
					],
					$param->name . '_value' => [
						'property' => $param->name . '_value',
						'type' => 'textFromNestedSection',
						'label' => 'Value to assign to this URL parameter if known',
						'required' => false,
						'description' => $param->defaultValue ? 'Default: '. $param->defaultValue : 'If the parameter value is known and can be tied to a setting, please enter it here. Otherwise, please leave this blank.',
						'hideInLists' => false,
					], 
					$param->name . '_kohaAdditionalField' => [
						'property' => $param->name . '_kohaAdditionalField',
						'type' => 'enumFromNestedSection',
						'label' => 'Name of the matching Koha additional field if any',
						'description' => 'If using a Koha additional field, select its name here. This dropdown only includes the Koha additional fields available to this URL parameter specifically, and may vary from one URL parameter to another.',
						'values' => $additionalFieldsWithDefault,
						'hideInLists' => false,
					],
				]				
			];
			
			if ($additionalFieldsWithDefault === ['none' => 'none']) {
				unset($propertyStructure['properties'][$param->name . '_kohaAdditionalField']);
			}
			$structure[] = $propertyStructure;
		}
		
		return $structure;
	}
}
