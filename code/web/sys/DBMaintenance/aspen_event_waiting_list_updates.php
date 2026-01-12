<?php /** @noinspection SqlResolve */

function getAspenEventWaitingListUpdates() {
	return [
		'create_aspen_event_waiting_list_table' => [
			'title' => 'Create Aspen Event Waiting List Table',
			'description' => 'Create the table to store waiting list information',
			'sql' => [
			   'CREATE TABLE IF NOT EXISTS user_aspen_event_instance_waiting_list (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					eventInstanceId INT NOT NULL,
					userId INT NOT NULL,
					position INT NOT NULL,
					status ENUM("waiting", "notified", "expired", "converted") DEFAULT "waiting",
					joinedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					notifiedAt TIMESTAMP NULL DEFAULT NULL,
					expiresAt TIMESTAMP NULL DEFAULT NULL,
					INDEX idx_eventInstanceId (eventInstanceId),
					INDEX idx_userId (userId),
					INDEX idx_status (status),
					INDEX idx_position (eventInstanceId, position),
					UNIQUE KEY unique_user_event (eventInstanceId, userId)
				) ENGINE=InnoDB'
			],
		], // create_aspen_event_waiting_list_table
		'add_available_number_of_waiting_list_seats_to_event' => [
			'title' => 'Add Available Number of Waiting List Seats to Event',
			'description' => 'Add a column to store the available number of seats on an event waiting list',
			'sql' => [
				'ALTER TABLE event_instance ADD COLUMN availableNumberOfWaitingListSeats INT DEFAULT NULL',
			],
		], // add_available_number_of_waiting_list_seats_to_event
		'add_can_register_information_to_event_waiting_lists' => [
			'title' => 'Add Can Register Information to Event Waiting Lists',
			'description' => 'Add informtaion about whether a user can register to an event to the event waiting list table',
			'sql' => [
				'ALTER TABLE user_aspen_event_instance_waiting_list ADD COLUMN canRegister TINYINT(1) DEFAULT 0',
				'ALTER TABLE user_aspen_event_instance_waiting_list ADD COLUMN canRegisterUntil DATETIME DEFAULT NULL',
			],
		], // add_can_register_information_to_event_waiting_lists
		'add_event_email_notification_preferences_to_user_db_table' =>[
			'title' => 'Add Event Email Notification Preferences to User DB Table',
			'description' => 'Add a column to store user preference about events notification emails',
			'sql' => [
				'ALTER TABLE user ADD COLUMN eventRegistrationNotificationsByEmail TINYINT DEFAULT 0',
			],
		], //add_event_email_notification_preferences_to_user_db_table
		'display_event_notifications_in_account' => [
			'title' => 'Display Event Notifications in Account',
			'description' => 'Add a column to track whether to allow event notifications in user accounts',
			'sql' => [
				'ALTER TABLE library ADD COLUMN displayEventNotificationsInAccount TINYINT DEFAULT 0',
			],
		], //display_event_notifications_in_account
		'add_column_to_track_if_toast_has_been_displayed' => [
			'title' => 'Add Column to Track if Toast Has Been Displayed',
			'description' => 'Add a column to track whether the toat for the event has been displayed',
			'sql' => [
				'ALTER TABLE user_aspen_event_instance_waiting_list ADD COLUMN toastShown TINYINT(1) DEFAULT 0',
			],
		], //add_column_to_track_if_toast_notification_has_been_displayed
		'add_column_to_track_if_toast_notifications_should_show_for_events' => [
			'title' => 'Add Column to Track if Toast Notifications Should Show For Events',
			'description' => 'Add a column to track whether toast notifications should display for events',
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowEventToastNotification TINYINT DEFAULT 0',
			],
		], //add_column_to_track_if_toast_notifications_should_show_for_events
		'add_event_toast_notification_preferences_to_user_db_table' =>[
			'title' => 'Add Event Toast Notification Preferences to User DB Table',
			'description' => 'Add a column to store user preference about events notification toasts',
			'sql' => [
				'ALTER TABLE user ADD COLUMN eventRegistrationNotificationsByToast TINYINT DEFAULT 0',
			],
		], //add_event_toast_notification_preferences_to_user_db_table
	];
}