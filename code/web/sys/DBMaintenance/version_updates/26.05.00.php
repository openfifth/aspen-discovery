<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_05_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien

		//kodi

		//yanjun

		//imani
		// Aspen Progressive Web Application(PWA) updates moved
		'create_aspen_pwa_module' => [
			'title' => 'Create Aspen Progressive Web Application(PWA) Module',
			'description' => 'Setup Aspen Progressive Web Application(PWA) (Progressive Web Application) module',
			'sql' => [
				"INSERT IGNORE INTO modules (name, indexName, backgroundProcess) VALUES ('Aspen Progressive Web Application(PWA)', '', '')",
			],
		],
		'create_aspen_pwa_settings' => [
			'title' => 'Create Aspen Progressive Web Application(PWA) Settings',
			'description' => 'Create database table for Aspen Progressive Web Application(PWA) settings',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS aspen_pwa_settings (
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
			'title' => 'Add Aspen Progressive Web Application(PWA) Setting Id',
			'description' => 'update library to include Aspen Progressive Web Application(PWA) setting ID to link to Aspen Progressive Web Application(PWA) settings',
			'sql' => [
				"ALTER TABLE library add column `AspenPWASettingId` int(11) Default -1;"
			],
		],
		'insert_aspen_pwa_permissions' => [
			'title' => 'Add Aspen Progressive Web Application(PWA) permissions',
			'description' => 'Add permisions for administering Aspen Progressive Web Application(PWA) and sending notifications',
			'sql' => [
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Administer Aspen Progressive Web Application(PWA) Settings','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 10, 'Controls if the user can change Aspen Progressive Web Application(PWA) Settings.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to All Libraries','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from all libraries.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to All Locations','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from all locations.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to Home Library','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from their home library.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to Home Location','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from their home location.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen PWA Notifications to Home Library Locations','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users for all locations that are part of their home library.');",
			],
		],
		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
