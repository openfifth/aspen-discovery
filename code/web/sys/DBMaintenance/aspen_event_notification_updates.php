<?php /** @noinspection SqlResolve */

function getAspenEventNotificationUpdates() {
	return [
		'create_table_to_store_aspen_event_notification_information' => [
			'title' => 'Create Table to Store Aspen Event Notification Information',
			'description' => 'Adds a table to store notification information for Apsen Native Events',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_aspen_event_notifications (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT(11) NOT NULL, 
					eventId INT(11) DEFAULT NULL,
					eventInstanceId INT(11) DEFAULT NULL, 
					notificationType VARCHAR(50) DEFAULT NULL,
					changeType VARCHAR(50) DEFAULT NULL, 
					toastShown TINYINT(1) DEFAULT 0, 
					emailSent TINYINT(1) DEFAULT 0,
					createdAt INT(11) NOT NULL,
					INDEX userId (userId), 
					INDEX eventId (eventId),
					INDEX eventInstanceId (eventInstanceId),
					INDEX toastShown (toastShown),
					INDEX notificationType (notificationType)
				)ENGINE = InnoDB',
			],
		], // create_table_to_store_aspen_event_notification_information
	];
}