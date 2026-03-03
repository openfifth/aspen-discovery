<?php
//ALTER TABLE library add column `aspenMobileSettingId` int(11) Default -1;
// insert into `permissions` VALUES (271,'Administer Aspen Mobile Settings','Aspen Mobile', 'Aspen Mobile', 10, 'Controls if the user can change Aspen Mobile Settings');
/** @noinspection SqlResolve */
function getAspenMobileUpdates() {
	return [
		'create_aspen_mobile_module' => [
			'title' => 'Create Aspen Mobile Module',
			'description' => 'Setup Aspen Mobile (Progressive Web Application) module',
			'sql' => [
				"INSERT IGNORE INTO modules (name, indexName, backgroundProcess) VALUES ('Aspen Mobile', '', '')",
			],
		],
		'create_aspen_mobile_settings' => [
			'title' => 'Create Aspen Mobile Settings',
			'description' => 'Create database table for Aspen Mobile settings',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS aspen_mobile_settings (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name varchar(50) NOT NULL,
					shortName varchar(50) NOT NULL,
					description varchar(200) NOT NULL,
					themeId int(11) NOT NULL,
					manifestID varchar(50) NOT NULL,
					startURL  varchar(50) DEFAULT '/',
					slug  varchar(50) NOT NULL,
					sha256CertFingerprint  varchar(200) NOT NULL,
					firebaseAPIKey varchar(50) NOT NULL,
					firebaseAuthDomain varchar(50) NOT NULL,
					firebaseProjectID varchar(50) NOT NULL,
					firebaseStorageBucket varchar(50) NOT NULL,
					firebaseMessagingSenderID varchar(50) NOT NULL,
					firebaseAppID varchar(50) NOT NULL,
					firebaseMeasurementID varchar(50) NOT NULL,
					vapidKey varchar(100) NOT NULL,
					serviceAccount varchar(5000) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
			]
			],
		'alter_user_notification_token' => [
			'title' => 'User Notification Token Update',
			'description' => 'Adding tokenType field to user notification Token',
			'sql' => [
				"SELECT count(*)
					INTO @exist
					FROM information_schema.columns 
					WHERE table_schema = database()
					and COLUMN_NAME = 'tokenType'
					AND table_name = 'user_notification_tokens';

					set @query = IF(@exist <= 0, 'alter table user_notification_tokens add column tokenType varchar(16) default \'expo\'', 'select \'Column Exists\' status');

					prepare stmt from @query;

					EXECUTE stmt;
					DEALLOCATE PREPARE stmt;",
			],
		],
		'alter_library_add_setting' => [
			'title' => 'Add Aspen Mobile Setting Id',
			'description' => 'update library to include aspen mobile setting ID to link to aspen mobile settings',
			'sql' => [
				"ALTER TABLE library add column `aspenMobileSettingId` int(11) Default -1;"
			],
		],
		'insert_aspen_mobile_permissions' => [
			'title' => 'Add Aspen Mobile permissions',
			'description' => 'Add permisions for administering aspen mobile and sending notifications',
			'sql' => [
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Administer Aspen Mobile Settings','Aspen Mobile', 'Aspen Mobile', 10, 'Controls if the user can change Aspen Mobile Settings.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Mobile Notifications to All Libraries','Aspen Mobile', 'Aspen Mobile', 6, 'Controls if the user can send notifications to Aspen Mobile users from all libraries.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Mobile Notifications to All Locations','Aspen Mobile', 'Aspen Mobile', 6, 'Controls if the user can send notifications to Aspen Mobile users from all locations.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Mobile Notifications to Home Library','Aspen Mobile', 'Aspen Mobile', 6, 'Controls if the user can send notifications to Aspen Mobile users from their home library.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Mobile Notifications to Home Location','Aspen Mobile', 'Aspen Mobile', 6, 'Controls if the user can send notifications to Aspen Mobile users from their home location.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Mobile Notifications to Home Library Locations','Aspen Mobile', 'Aspen Mobile', 6, 'Controls if the user can send notifications to Aspen Mobile users for all locations that are part of their home library.');",
			],
		]
	];
}