<?php /** @noinspection SqlResolve */

function getAspenEventWaitingListUpdates() {
	return [
		'add_waitingList_to_events_and_instances' => [
			'title' => 'Add waitingList to Events and Event Instances',
			'description' => 'Add waiting list column to events.',
			'sql' => [
				'ALTER TABLE event ADD COLUMN IF NOT EXISTS waitingList TINYINT(1) DEFAULT 0',
				'ALTER TABLE event_instance ADD COLUMN IF NOT EXISTS waitingList TINYINT(1) DEFAULT NULL',
			],
		], // add_waitingList_to_events_and_instance
		'add_waitingListNumberOfSeats_to_events' => [
			'title' => 'Add waitingListNumberOfSeats to Events',
			'description' => 'Add waiting list number of seats column to Events and Event Instances',
			'sql' => [
				'ALTER TABLE event ADD COLUMN IF NOT EXISTS waitingListNumberOfSeats SMALLINT UNSIGNED DEFAULT NULL',
				'ALTER TABLE event_instance ADD COLUMN IF NOT EXISTS waitingListNumberOfSeats SMALLINT UNSIGNED DEFAULT NULL',
			],
		], // add_waitingListNumberOfSeats_to_events
		'replace_registered_with_status_in_user_aspen_event_instance_registrations' => [
			'title' => 'Replace Registered with Status in User Aspen Event Instance Registrations',
			'description' => 'Update the aspen event instance registration table to support waiting lists',
			'continueOnError' => true,
			'sql' => [
				  'ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS createdAt DATETIME DEFAULT CURRENT_TIMESTAMP',
				  'ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS notifiedAt DATETIME DEFAULT NULL',
				  'ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT "waiting"',
  				  'UPDATE user_aspen_event_instance_registrations SET status = "registered" WHERE registered = 1',
  				  'ALTER TABLE user_aspen_event_instance_registrations DROP COLUMN IF EXISTS registered',
			],
		], // replace_registered_with_status_in_user_aspen_event_instance_registrations
		'add_waiting_list_invite_expiry_hours_to_event_type' => [
			'title' => 'Add Waiting List Invite Expiry Hours to Event Type',
			'description' => 'Add a configurable invite expiry window for waiting list invitations',
			'sql' => [
				'ALTER TABLE event_type ADD COLUMN IF NOT EXISTS waitingListInviteExpiryHours INT DEFAULT 24',
			],
		], // add_waiting_list_invite_expiry_hours_to_event_type
	];
}