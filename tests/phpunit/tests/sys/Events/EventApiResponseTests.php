<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for the harvest API response formatter on Event (toHarvestApiResponse)
 * and the supporting recurrence / date-time helpers.
 *
 * The expected key sets encode the response contract consumed by external harvesters
 * (LibraryOn) — changing them is a breaking API change.
 */
class EventApiResponseTests extends TestCase {

	private const EXPECTED_HARVEST_KEYS = [
		'id',
		'title',
		'description',
		'isFree',
		'venueName',
		'organizer',
		'eventImageURL',
		'tags',
		'isRecurring',
		'recurrencePattern',
		'capacity',
		'private',
		'instances',
	];

	private const EXPECTED_INSTANCE_KEYS = [
		'id',
		'startDateTime',
		'endDateTime',
		'status',
		'bookingUrl',
		'ticketAvailability',
	];

	private EventType $eventType;

	public function __construct(string $name) {
		parent::__construct($name);
		require_once __DIR__ . '/../../../../../code/web/sys/Events/Event.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventInstance.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventType.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/EventsIndexingSetting.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Events/UserAspenEventInstanceRegistration.php';
		require_once __DIR__ . '/../../../../../code/web/sys/Account/User.php';
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

		$aspen_db->exec("DELETE FROM user_aspen_event_instance_registrations");
		$aspen_db->exec("DELETE FROM event_instance");
		$aspen_db->exec("DELETE FROM event");
		$aspen_db->exec("DELETE FROM user WHERE source = 'phpunit'");

		parent::tearDown();
	}

	private function insertEvent(array $properties = []): Event {
		$event = new Event();
		$event->title = 'PHPUnit API Event';
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

	private function insertUser(int $id): User {
		$user = new User();
		$user->source = 'phpunit';
		$user->username = "phpunit_user_$id";
		$user->firstname = 'Test';
		$user->lastname = "User_$id";
		$user->displayName = "Test User $id";
		$user->created = date('Y-m-d H:i:s');
		$user->homeLocationId = 1;
		$user->myLocation1Id = 1;
		$user->myLocation2Id = 1;
		$user->unique_ils_id = "phpunit_user_$id";
		$user->insert();
		return $user;
	}

	private function insertRegistration(int $userId, int $eventInstanceId, string $status): void {
		$registration = new UserAspenEventInstanceRegistration();
		$registration->userId = $userId;
		$registration->eventInstanceId = $eventInstanceId;
		$registration->status = $status;
		$registration->createdAt = date('Y-m-d H:i:s');
		$registration->insert();
	}

	public function testGetRecurrenceNonRepeatingIsNull(): void {
		$event = $this->insertEvent(['recurrenceOption' => 1]);
		$this->assertNull($event->getRecurrence());
	}

	public function testGetRecurrenceSimpleTypes(): void {
		$event = $this->insertEvent(['recurrenceOption' => 3]);
		$this->assertSame(['type' => 'weekly'], $event->getRecurrence());
	}

	public function testGetRecurrenceCustomWeekly(): void {
		$event = $this->insertEvent([
			'recurrenceOption' => 7,
			'recurrenceFrequency' => 2,
			'recurrenceInterval' => 2,
			'weekDays' => '1,3',
			'endOption' => 2,
			'recurrenceCount' => 10,
		]);

		$recurrence = $event->getRecurrence();

		$this->assertSame('custom', $recurrence['type']);
		$this->assertSame('weekly', $recurrence['frequency']);
		$this->assertSame(2, $recurrence['interval']);
		$this->assertSame([
			'Monday',
			'Wednesday',
		], $recurrence['weekDays']);
		$this->assertSame(10, $recurrence['endsAfter']);
	}

	public function testGetRecurrenceEndsOn(): void {
		$event = $this->insertEvent([
			'recurrenceOption' => 2,
			'endOption' => 1,
			'recurrenceEnd' => '2031-01-01',
		]);

		$recurrence = $event->getRecurrence();

		$this->assertSame('daily', $recurrence['type']);
		$this->assertSame('2031-01-01', $recurrence['endsOn']);
	}

	public function testInstanceStartAndEndDateTime(): void {
		$event = $this->insertEvent();
		$instance = $this->insertInstance($event, [
			'date' => '2030-05-01',
			'time' => '10:30:00',
			'length' => 90,
		]);

		$this->assertSame('2030-05-01 10:30', $instance->getStartDateTime()->format('Y-m-d H:i'));
		$this->assertSame('2030-05-01 12:00', $instance->getEndDateTime()->format('Y-m-d H:i'));
	}

	public function testInstanceEndDateTimeFallsBackToEventLength(): void {
		$event = $this->insertEvent(['eventLength' => 45]);
		$instance = $this->insertInstance($event, [
			'date' => '2030-05-01',
			'time' => '10:00:00',
			'length' => 0,
		]);

		$this->assertSame('2030-05-01 10:45', $instance->getEndDateTime()->format('Y-m-d H:i'));
	}

	public function testToHarvestApiResponseShape(): void {
		$event = $this->insertEvent(['description' => '<p>Harvest <i>me</i></p>']);
		$instance = $this->insertInstance($event);

		$response = $event->toHarvestApiResponse();

		$this->assertEqualsCanonicalizing(self::EXPECTED_HARVEST_KEYS, array_keys($response));
		$this->assertSame((int)$event->id, $response['id']);
		$this->assertSame('Harvest me', $response['description']);
		$this->assertTrue($response['isFree']);
		$this->assertFalse($response['isRecurring']);
		$this->assertNull($response['recurrencePattern']);
		$this->assertNull($response['capacity']);
		$this->assertFalse($response['private']);
		$this->assertSame([], $response['tags']);

		$location = new Location();
		$location->locationId = 1;
		$this->assertTrue($location->find(true));
		$this->assertSame($location->displayName, $response['venueName']);
		$library = new Library();
		$library->libraryId = $location->libraryId;
		$library->find(true);
		$this->assertSame($library->displayName, $response['organizer']);

		$this->assertCount(1, $response['instances']);
		$instanceResponse = $response['instances'][0];
		$this->assertEqualsCanonicalizing(self::EXPECTED_INSTANCE_KEYS, array_keys($instanceResponse));
		$this->assertSame((int)$instance->id, $instanceResponse['id']);
		$this->assertSame($instance->getStartDateTime()->format('c'), $instanceResponse['startDateTime']);
		$this->assertSame($instance->getEndDateTime()->format('c'), $instanceResponse['endDateTime']);
		$this->assertTrue($instanceResponse['status']);
		$this->assertMatchesRegularExpression('#/AspenEvents/aspenEvent_\d+_' . $instance->id . '/Event$#', $instanceResponse['bookingUrl']);
		$this->assertEqualsCanonicalizing([
			'isSoldOut',
			'hasAvailableTickets',
		], array_keys($instanceResponse['ticketAvailability']));
	}

	public function testToHarvestApiResponseRecurring(): void {
		$event = $this->insertEvent(['recurrenceOption' => 3]);
		$this->insertInstance($event);

		$response = $event->toHarvestApiResponse();

		$this->assertTrue($response['isRecurring']);
		$this->assertSame('weekly', $response['recurrencePattern']);
	}

	public function testToHarvestApiResponseDateFilters(): void {
		$event = $this->insertEvent();
		$this->insertInstance($event, ['date' => date('Y-m-d', strtotime('+5 days'))]);
		$inWindow = $this->insertInstance($event, ['date' => date('Y-m-d', strtotime('+10 days'))]);
		$this->insertInstance($event, ['date' => date('Y-m-d', strtotime('+15 days'))]);

		$response = $event->toHarvestApiResponse([
			'startDate' => date('Y-m-d', strtotime('+7 days')),
			'endDate' => date('Y-m-d', strtotime('+12 days')),
		]);

		$this->assertCount(1, $response['instances']);
		$this->assertSame((int)$inWindow->id, $response['instances'][0]['id']);
	}

	public function testToHarvestApiResponseExcludesDeletedInstances(): void {
		$event = $this->insertEvent();
		$this->insertInstance($event, ['deleted' => 1]);
		$kept = $this->insertInstance($event, ['date' => date('Y-m-d', strtotime('+8 days'))]);

		$response = $event->toHarvestApiResponse();

		$this->assertCount(1, $response['instances']);
		$this->assertSame((int)$kept->id, $response['instances'][0]['id']);
	}

	public function testTicketAvailabilityWithoutRegistration(): void {
		$event = $this->insertEvent(['numberOfSeats' => 5]);
		$this->insertInstance($event);

		$availability = $event->toHarvestApiResponse()['instances'][0]['ticketAvailability'];

		$this->assertFalse($availability['isSoldOut']);
		$this->assertTrue($availability['hasAvailableTickets']);
	}

	public function testTicketAvailabilityUnlimitedSeats(): void {
		$event = $this->insertEvent(['registrationRequired' => 1]);
		$this->insertInstance($event);

		$availability = $event->toHarvestApiResponse()['instances'][0]['ticketAvailability'];

		$this->assertFalse($availability['isSoldOut']);
		$this->assertTrue($availability['hasAvailableTickets']);
	}

	public function testTicketAvailabilityWithOpenSeats(): void {
		$event = $this->insertEvent([
			'registrationRequired' => 1,
			'numberOfSeats' => 2,
		]);
		$instance = $this->insertInstance($event);
		$user = $this->insertUser(1);
		$this->insertRegistration($user->id, $instance->id, 'registered');

		$availability = $event->toHarvestApiResponse()['instances'][0]['ticketAvailability'];

		$this->assertFalse($availability['isSoldOut']);
		$this->assertTrue($availability['hasAvailableTickets']);
	}

	public function testTicketAvailabilitySoldOutByRegistrations(): void {
		$event = $this->insertEvent([
			'registrationRequired' => 1,
			'numberOfSeats' => 1,
		]);
		$instance = $this->insertInstance($event);
		$user = $this->insertUser(1);
		$this->insertRegistration($user->id, $instance->id, 'registered');

		$availability = $event->toHarvestApiResponse()['instances'][0]['ticketAvailability'];

		$this->assertTrue($availability['isSoldOut']);
		$this->assertFalse($availability['hasAvailableTickets']);
	}

	public function testTicketAvailabilitySoldOutWhileWaitingListQueued(): void {
		$event = $this->insertEvent([
			'registrationRequired' => 1,
			'numberOfSeats' => 2,
			'waitingList' => 1,
		]);
		$instance = $this->insertInstance($event, ['waitingList' => 1]);
		$user = $this->insertUser(1);
		$this->insertRegistration($user->id, $instance->id, 'waiting');

		$availability = $event->toHarvestApiResponse()['instances'][0]['ticketAvailability'];

		$this->assertTrue($availability['isSoldOut']);
		$this->assertFalse($availability['hasAvailableTickets']);
	}
}
