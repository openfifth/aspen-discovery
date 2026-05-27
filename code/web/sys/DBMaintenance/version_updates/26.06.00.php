<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_06_00(): array {
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
		'permissions_create_events_localhop' => [
			'title' => 'Alters permissions for Events',
			'description' => 'Create permissions for LocalHop',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Events', 'Administer LocalHop Settings', 'Events', 20, 'Allows the user to administer integration with LocalHop for all libraries.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer LocalHop Settings'))",
			],
		],
		// permissions_create_events_localhop
		'localhop_settings' => [
			'title' => 'Define events settings for LocalHop integration',
			'description' => 'Initial setup of the LocalHop integration',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS localhop_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL UNIQUE,
					baseUrl VARCHAR(255) NOT NULL,
					eventsInLists tinyint(1) default 1,
					bypassAspenEventPages tinyint(1) default 0,
					registrationModalBody mediumtext,
					registrationModalBodyApp varchar(500),
					numberOfDaysToIndex INT DEFAULT 365
				) ENGINE INNODB',
			],
		], // localhop_settings
		'localhop_events' => [
			'title' => 'LocalHop Event Data',
			'description' => 'Set up table to store events data for LocalHop',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS localhop_events (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					settingsId INT NOT NULL,
					externalId varchar(150) NOT NULL,
					title varchar(255) NOT NULL,
					rawChecksum BIGINT,
					rawResponse MEDIUMTEXT,
					deleted TINYINT default 0,
					UNIQUE (settingsId, externalId)
				)',
			],
		], // localhop_events

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen

		//other

	];
}
