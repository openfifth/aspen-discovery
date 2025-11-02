<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventField.php';
require_once ROOT_DIR . '/sys/Events/EventFieldSetField.php';
require_once ROOT_DIR . '/sys/Events/EventType.php';

class EventFieldCalendarOptions extends DataObject
{
	public $__table = 'event_field_calendar_options';
	public $id;
	public $weight;
	public $calendarDisplaySettingId;
	public $eventFieldId;
	public $displayedOnline;
	public $printedCalendar;
	public $printedAgenda;

	static $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array
	{
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		$eventFields = EventField::getEventFieldList(true);
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'integer',
				'label' => 'Weight',
				'description' => 'The sort order',
				'default' => 0,
			],
			'calendarDisplaySettingId' => [
				'property' => 'calendarDisplaySettingId',
				'type' => 'label',
				'label' => 'Calendar Setting Id',
				'description' => 'The id of the calendar setting',
			],
			'eventFieldId' => [
				'property' => 'eventFieldId',
				'type' => 'enum',
				'label' => 'Event Field',
				'values' => $eventFields,
			],
			'displayedOnline' => [
				'property' => 'displayedOnline',
				'type' => 'checkbox',
				'label' => 'Displayed Online',
			],
			'printedCalendar' => [
				'property' => 'printedCalendar',
				'type' => 'checkbox',
				'label' => 'Printed Calendar',
			],
			'printedAgenda' => [
				'property' => 'printedAgenda',
				'type' => 'checkbox',
				'label' => 'Printed Agenda',
			]
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}
}