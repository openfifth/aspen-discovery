<?php /** @noinspection SqlResolve */

function getSeftonEventUpdates() {
	return [
		'rebuild_event_registration_tables' => [
			'title' => 'Rebuild Event Registration Tables',
			'description' => 'Drop stale test data tables and recreate registrations with the correct schema',
			'continueOnError' => true,
			'sql' => [
				'DROP TABLE IF EXISTS user_aspen_event_notifications',
				'DROP TABLE IF EXISTS user_aspen_event_instance_waiting_list',
				'TRUNCATE TABLE user_aspen_event_instance_registrations_event_field',
				'DROP TABLE IF EXISTS user_aspen_event_instance_registrations',
				'CREATE TABLE user_aspen_event_instance_registrations (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT NOT NULL,
					eventInstanceId INT NOT NULL,
					success TINYINT(1) DEFAULT NULL,
					attended TINYINT(1) DEFAULT NULL,
					registeredByStaffId INT DEFAULT NULL,
					createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
					notifiedAt DATETIME DEFAULT NULL,
					status VARCHAR(20) NOT NULL DEFAULT "waiting"
				) ENGINE = InnoDB',
			],
		], //rebuild_event_registration_tables
		'add_savedByStaffId_to_user_events_entry' => [
			'title' => 'Add savedByStaffId to User Events Entry',
			'description' => 'Track which staff member saved an event entry on behalf of a patron',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE user_events_entry ADD COLUMN IF NOT EXISTS savedByStaffId INT DEFAULT NULL',
			],
		], //add_savedByStaffId_to_user_events_entry
	];
}
