<?php
/** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/DB/DataObject.php';

class LibraryUserDefinedField extends DataObject {
	public $__table = 'library_user_defined_field';
	public $id;
	public $libraryId;
	public $fieldNumber;
	public $label;
	public $required;
	public $maxLength;

	static function getObjectStructure(string $context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id.',
				'hideInLists' => true,
			],
			'libraryId' => [
				'property' => 'libraryId',
				'type' => 'label',
				'label' => 'Library Id',
				'description' => 'The library to which this this field belongs.',
				'hideInLists' => true,
			],
			'fieldNumber' => [
				'property' => 'fieldNumber',
				'type' => 'text',
				'label' => 'User Defined Field',
				'description' => 'The user defined field.',
				'readOnly' => true,
				'maxLength' => 30,
			],
			'label' => [
				'property' => 'label',
				'type' => 'text',
				'label' => 'Label',
				'description' => 'The label to display for this field in self registration. Leave blank to hide the field.',
				'maxLength' => 255,
				'default' => '',
			],
			'required' => [
				'property' => 'required',
				'type' => 'checkbox',
				'label' => 'Required',
				'description' => 'Whether this field is required during self registration.',
				'default' => false,
			],
			'maxLength' => [
				'property' => 'maxLength',
				'type' => 'integer',
				'label' => 'Max Length',
				'description' => 'Maximum length allowed for this field.',
				'default' => 255,
				'min' => 1,
				'max' => 255,
			],
		];
	}

	public function getNumericColumnNames(): array {
		return ['libraryId', 'required', 'maxLength'];
	}
}
