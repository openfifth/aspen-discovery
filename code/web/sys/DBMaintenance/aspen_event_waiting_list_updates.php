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
	];
}