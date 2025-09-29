<?php /** @noinspection PhpMissingFieldTypeInspection */

class Grouping_Scope extends DataObject {
	public $__table = 'scope';
	public $id;
	public $name;
	public $isLibraryScope;
	public $isLocationScope;

	public function getNumericColumnNames() : array {
		return ['id', 'isLibraryScope', 'isLocationScope'];
	}
}