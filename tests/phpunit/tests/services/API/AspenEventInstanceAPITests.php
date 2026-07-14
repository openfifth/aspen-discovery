<?php

namespace services\API;

use AspenEventInstanceAPI;
use Event;
use EventInstance;
use EventType;
use EventsIndexingSetting;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the AspenEventInstanceAPI endpoint consumed by external
 * harvesters (LibraryOn): response envelope, filtering, and authorization
 * failure behavior.
 */
class AspenEventInstanceAPITests extends TestCase {

	private const EXPECTED_PAGINATION_KEYS = [
		'success',
		'totalResults',
		'page',
		'pageSize',
		'totalPages',
		'items',
	];

	private EventType $eventType;

	public function __construct(string $name) {
		parent::__construct($name);
		require_once __DIR__ . '/../../../../../code/web/services/API/AspenEventInstanceAPI.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/Event.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventInstance.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventType.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventsIndexingSetting.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->eventType = new EventType();
		$this->eventType->title = 'PHPUnit Test Type';
		if (!$this->eventType->find(true)) {
			$this->eventType->eventInformationFieldSetId = 1;
			$this->eventType->eventRegistrationFieldSetId = 1;
			$this->assertNotFalse($this->eventType->insert());
		}

		$indexingSetting = new EventsIndexingSetting();
		if (!$indexingSetting->find(true)) {
			$indexingSetting->name = 'PHPUnit Events';
			$indexingSetting->insert();
		}
	}

	protected function tearDown(): void {
		global $aspen_db;

		$aspen_db->exec("DELETE FROM event_instance");
		$aspen_db->exec("DELETE FROM event");

		foreach ([
			'page',
			'pageSize',
			'locationId',
			'eventId',
			'startDate',
			'endDate',
			'username',
			'password',
		] as $param) {
			unset($_REQUEST[$param]);
		}
		http_response_code(200);

		parent::tearDown();
	}

	private function insertEvent(string $title, array $properties = []): Event {
		$event = new Event();
		$event->title = $title;
		$event->description = 'A test event';
		$event->private = 0;
		$event->locationId = 1;
		$event->eventTypeId = $this->eventType->id;
		$event->startDate = date('Y-m-d', strtotime('+7 days'));
		$event->startTime = '10:00:00';
		$event->eventLength = 60;
		foreach ($properties as $property => $value) {
			$event->$property = $value;
		}
		$this->assertNotFalse($event->insert());
		return $event;
	}

	private function insertInstance(Event $event, array $properties = []): EventInstance {
		$instance = new EventInstance();
		$instance->eventId = $event->id;
		$instance->date = date('Y-m-d', strtotime('+7 days'));
		$instance->time = '10:00:00';
		$instance->length = 60;
		$instance->status = 1;
		foreach ($properties as $property => $value) {
			$instance->$property = $value;
		}
		$this->assertNotFalse($instance->insert());
		return $instance;
	}

	public function testGetEventInstancesShape(): void {
		$event = $this->insertEvent('Instance Event');
		$this->insertInstance($event);

		$response = (new AspenEventInstanceAPI())->getEventInstances();

		$this->assertEqualsCanonicalizing(self::EXPECTED_PAGINATION_KEYS, array_keys($response));
		$this->assertTrue($response['success']);
		$this->assertSame(1, $response['totalResults']);
		$this->assertCount(1, $response['items'][0]['instances']);
	}

	public function testGetEventInstancesFiltersByEventId(): void {
		$wanted = $this->insertEvent('Wanted Event');
		$this->insertInstance($wanted);
		$other = $this->insertEvent('Other Event');
		$this->insertInstance($other);

		$_REQUEST['eventId'] = $wanted->id;
		$response = (new AspenEventInstanceAPI())->getEventInstances();

		$this->assertSame(1, $response['totalResults']);
		$this->assertSame((int)$wanted->id, $response['items'][0]['id']);
	}

	public function testGetEventInstancesDateWindowExcludesEventsWithoutInstances(): void {
		$inWindow = $this->insertEvent('In Window');
		$this->insertInstance($inWindow, ['date' => date('Y-m-d', strtotime('+10 days'))]);
		$outOfWindow = $this->insertEvent('Out Of Window');
		$this->insertInstance($outOfWindow, ['date' => date('Y-m-d', strtotime('+30 days'))]);

		$_REQUEST['startDate'] = date('Y-m-d', strtotime('+7 days'));
		$_REQUEST['endDate'] = date('Y-m-d', strtotime('+12 days'));
		$response = (new AspenEventInstanceAPI())->getEventInstances();

		$this->assertSame(1, $response['totalResults']);
		$this->assertSame('In Window', $response['items'][0]['title']);
		$this->assertCount(1, $response['items'][0]['instances']);
	}

	public function testGetEventInstancesExcludesPrivateAndDeletedEvents(): void {
		$public = $this->insertEvent('Public Event');
		$this->insertInstance($public);
		$private = $this->insertEvent('Private Event', ['private' => 1]);
		$this->insertInstance($private);
		$deleted = $this->insertEvent('Deleted Event', ['deleted' => 1]);
		$this->insertInstance($deleted);

		$response = (new AspenEventInstanceAPI())->getEventInstances();

		$this->assertSame(1, $response['totalResults']);
		$this->assertSame('Public Event', $response['items'][0]['title']);
	}

	public function testGetPrivateEventInstancesRequiresUser(): void {
		$response = (new AspenEventInstanceAPI())->getPrivateEventInstances();

		$this->assertFalse($response['success']);
		$this->assertSame('A valid user is required to view private events.', $response['message']);
		$this->assertSame(401, http_response_code());
	}
}
