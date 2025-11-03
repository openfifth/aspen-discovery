<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventsFacetGroup.php';

class LibraryEventsSetting extends DataObject {
	public $__table = 'library_events_setting';
	public $id;
	public $settingSource;
	public $settingId;
	public $libraryId;
}