<?php

/** @noinspection PhpUnused */
function getUpdates26_Q1_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

        //lucas
		'indexing_profiles_add_default_values' => [
			'title' => 'Indexing Profiles - Add Default Values',
			'description' => 'Add default values to required columns in indexing_profiles table to support multi-step form creation via UI.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE indexing_profiles MODIFY COLUMN recordNumberTag char(3) NOT NULL DEFAULT ''",
				"ALTER TABLE indexing_profiles MODIFY COLUMN recordNumberPrefix varchar(10) NOT NULL DEFAULT ''",
				"ALTER TABLE indexing_profiles MODIFY COLUMN itemTag char(3) NOT NULL DEFAULT ''",
				"ALTER TABLE indexing_profiles MODIFY COLUMN marcPath varchar(100) NOT NULL DEFAULT ''",
				"ALTER TABLE indexing_profiles MODIFY COLUMN indexingClass varchar(50) NOT NULL DEFAULT ''",
			]
		], //indexing_profiles_add_default_values
		'sierra_phone_fields' => [
			'title' => 'Sierra Phone Fields',
			'description' => 'Add configurable phone fields for Sierra Phone and Work Phone',
			'sql' => [
				"ALTER TABLE library ADD COLUMN phoneField CHAR(1) DEFAULT 't'",
				"ALTER TABLE library ADD COLUMN workPhoneField CHAR(1) DEFAULT 'p'"
			]
		], //sierra_phone_fields
		//mark n
		'ip_address_bypass_failed_login_checks' => [
			'title' => 'IP Address Bypass Failed Login checks',
			'description' => 'Add Bypass Failed Login checks to IP Address settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE ip_lookup ADD COLUMN bypassFailedLoginChecks TINYINT(1) DEFAULT 0',
			]
		], //ip_address_bypass_failed_login_checks
		'sierra_address_line_for_city_state_zip' => [
			'title' => 'Sierra Address Line for City State Zip',
			'description' => 'Allow the address line which is used for City State Zip to be defined',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN sierraAddressLineForCityState TINYINT(1) DEFAULT 2',
				'ALTER TABLE library ADD COLUMN sierraZipOnSameLineAsCityState TINYINT(1) DEFAULT 1',
			]
		], //sierra_address_line_for_city_state_zip
		'force_regrouping_of_hoopla' => [
			'title' => 'Force Regrouping of Hoopla',
			'description' => 'Force Regrouping of Hoopla',
			'sql' => [
				"UPDATE hoopla_settings set regroupAllRecords = 1"
			]
		], //force_regrouping_of_hoopla

		// 26_Q1.o5th-specific backports
		'update_aspenEventsToInclude_default' => [
			'title' => 'Update AspenEventsToInclude Default',
			'description' => 'Have aspenEventsToInclude default to 0 (do not display events as a search source)',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library MODIFY COLUMN aspenEventsToInclude INT DEFAULT 0",
			],
		], //update_aspenEventsToInclude_default
		'migrate_sendgrid_url_to_settings' => [
			'title' => 'Migrate SendGrid URL to Settings',
			'description' => 'The URL for sendGrid should be customisable as it is region specific',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE sendgrid_settings ADD COLUMN baseUrl VARCHAR(255) DEFAULT null",
			],
		], //migrate_sendgrid_url_to_settings
	];
}