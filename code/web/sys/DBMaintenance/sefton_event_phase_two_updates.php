<?php /** @noinspection SqlResolve */

/**
 * This custom upgrade script is for use between 26.04.o5th.SEFTON and 26.04.o5th.SEFTON.02
 * Aim:
 * 		- to migrate registration status (success = 1 BECOMES status = 'registered')
 * 		- to migrate waiting list data (from a _waiting_list row into a _registration row with status 'waiting')
 * 		- to ensure that DB updates that are present in the _events_update file are detected by the system.
 */

function getSeftonEventPhaseTwoUpdates() {
	return [

		// order matters here
		'refactor_registrations_add_status_and_timestamps' => [
			'title' => 'Refactor Registrations: Add status, createdAt, notifiedAt',
			'description' => 'Adds status/createdAt/notifiedAt columns to registrations. Existing confirmed registrations are set to status=registered. createdAt is converted from the dateRegistered unix timestamp.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT NULL",
				"UPDATE user_aspen_event_instance_registrations SET status = 'registered' WHERE status IS NULL",
				"ALTER TABLE user_aspen_event_instance_registrations MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'waiting'",
				"ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS createdAt DATETIME DEFAULT NULL",
				"ALTER TABLE user_aspen_event_instance_registrations ADD COLUMN IF NOT EXISTS notifiedAt DATETIME DEFAULT NULL",
				"UPDATE user_aspen_event_instance_registrations SET createdAt = FROM_UNIXTIME(dateRegistered) WHERE dateRegistered IS NOT NULL AND createdAt IS NULL",
				"ALTER TABLE user_aspen_event_instance_registrations DROP COLUMN IF EXISTS dateRegistered",
				"ALTER TABLE user_aspen_event_instance_registrations DROP COLUMN IF EXISTS cancelled",
			],
		], // refactor_registrations_add_status_and_timestamps

		'migrate_waiting_list_to_registrations' => [
			'title' => 'Migrate Waiting List Entries into Registrations',
			'description' => 'Moves all waiting list entries from user_aspen_event_instance_waiting_list back into user_aspen_event_instance_registrations with status=waiting, preserving join time and notified time.',
			'continueOnError' => false,
			'sql' => [
				// status will be waiting by default which is why we don't specify it
				"INSERT INTO user_aspen_event_instance_registrations (userId, eventInstanceId, status, createdAt, notifiedAt) 
				SELECT userId, eventInstanceId, 'waiting', joinedAt, notifiedAt
				FROM user_aspen_event_instance_waiting_list",
				"DROP TABLE IF EXISTS user_aspen_event_instance_waiting_list",
			],
		], // migrate_waiting_list_to_registrations

		'create_attendee_category_tables' => [
			'title' => 'Create Attendee Category Tables',
			'description' => 'Creates aspen_event_attendee_category, event_type_attendee_category, and user_aspen_event_instance_registration_attendee',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS aspen_event_attendee_category (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(50) NOT NULL,
					staffDescription VARCHAR(255) NOT NULL,
					publicDescription VARCHAR(255) NOT NULL,
					UNIQUE KEY name (name)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
				"CREATE TABLE IF NOT EXISTS event_type_attendee_category (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					eventTypeId INT(11) NOT NULL,
					attendeeCategoryId INT(11) NOT NULL,
					maxAttendees INT(11) NOT NULL DEFAULT 1,
					UNIQUE KEY eventTypeId (eventTypeId, attendeeCategoryId)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
				"CREATE TABLE IF NOT EXISTS user_aspen_event_instance_registration_attendee (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					registrationId INT(11) NOT NULL,
					attendeeCategoryId INT(11) NOT NULL,
					count INT(11) NOT NULL DEFAULT 0,
					UNIQUE KEY registrationId (registrationId, attendeeCategoryId)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			],
		], // create_attendee_category_tables

		'add_savedByStaffId_to_user_events_entry' => [
			'title' => 'Add savedByStaffId to User Events Entry',
			'description' => 'Tracks which staff member saved an event entry on behalf of a patron',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user_events_entry ADD COLUMN IF NOT EXISTS savedByStaffId INT DEFAULT NULL",
			],
		], // add_savedByStaffId_to_user_events_entry

		'add_waitingListInviteExpiryHours_to_event_type' => [
			'continueOnError' => false,
			'title' => 'Add waitingListInviteExpiryHours to Event Type',
			'description' => 'Adds configurable invite expiry window (hours) to event_type',
			'sql' => [
				"ALTER TABLE event_type ADD COLUMN IF NOT EXISTS waitingListInviteExpiryHours INT DEFAULT 24",
			],
		], // add_waitingListInviteExpiryHours_to_event_type

		'library_event_registration_settings_update' => [
			'title' => 'Library Event Registration Settings Update',
			'description' => 'Adds allowEventRegistration and drops allowEventToastNotification',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN IF NOT EXISTS allowEventRegistration TINYINT(1) NOT NULL DEFAULT 0",
				"ALTER TABLE library DROP COLUMN IF EXISTS allowEventToastNotification",
			],
		], // library_event_registration_settings_update

		'drop_event_toast_notification_user_preference' => [
			'title' => 'Drop Event Toast Notification User Preference',
			'description' => 'Removes eventRegistrationNotificationsByToast from user; email consent in eventRegistrationNotificationsByEmail is retained',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user DROP COLUMN IF EXISTS eventRegistrationNotificationsByToast",
			],
		], // drop_event_toast_notification_user_preference

		'drop_availableNumberOfWaitingListSeats_from_event_instance' => [
			'title' => 'Drop availableNumberOfWaitingListSeats from Event Instance',
			'description' => 'Removes availableNumberOfWaitingListSeats from event_instance as capacity is managed via the registrations table',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE event_instance DROP COLUMN IF EXISTS availableNumberOfWaitingListSeats",
			],
		], // drop_availableNumberOfWaitingListSeats_from_event_instance

		'drop_event_notifications_table' => [
			'title' => 'Drop Event Notifications Table',
			'description' => 'Removes user_aspen_event_notifications; email notification consent is retained in user.eventRegistrationNotificationsByEmail',
			'continueOnError' => false,
			'sql' => [
				"DROP TABLE IF EXISTS user_aspen_event_notifications",
			],
		], // drop_event_notifications_table

		'add_enable_patron_ils_registration_by_staff' => [
			'title' => 'Add Enable Patron ILS Registration By Staff Library Setting',
			'description' => 'Add library setting to enable staff to register new ILS patrons from within Aspen.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN enablePatronIlsRegistrationByStaff TINYINT(1) NOT NULL DEFAULT 0",
			],
		], //add_enable_patron_ils_registration_by_staff
		'add_register_new_ils_patrons_permissions' => [
			'title' => 'Add Register New ILS Patrons Permission Family',
			'description' => 'Add Patron Management permissions allowing staff to register new ILS patrons, scoped by home library / location, mirroring the Masquerade scoping pattern.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('Patron Management', 'Register New ILS Patrons for any home library', '', 30, 'Allows the user to register new ILS patrons with any home library.'),
					('Patron Management', 'Register New ILS Patrons for patrons with same home library', '', 31, 'Allows the user to register new ILS patrons whose home library matches the staff member''s.'),
					('Patron Management', 'Register New ILS Patrons for patrons with same home location', '', 32, 'Allows the user to register new ILS patrons whose home location matches the staff member''s.')
				",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Register New ILS Patrons for any home library'))",
			],
		], //add_register_new_ils_patrons_permissions
	];
}
