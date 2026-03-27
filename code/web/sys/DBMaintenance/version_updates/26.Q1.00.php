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

		// 26_Q1.o5th customer-specific backports
        'add_option_to_add_location_to_event_thumbail_image' => [
            'title' => 'Add Option to Add Location to Event Thumnail Image',
            'description' => 'Add ability to choose to add event location to event thumbnail image',
            'sql' => [
                "ALTER TABLE event ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0",
                "ALTER TABLE user_events_entry ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0"
            ]
        ], //add_option_to_add_location_to_event_thumbnail_image
        'add_option_to_set_display_event_location_on_event_type' => [
            'title' => 'Add Option to Set Display Event Location On Event Type',
            'description' => 'Add ability to choose to add event location to event thumbnail image at the event type level',
            'sql' => [
                "ALTER TABLE event_type ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0",
            ]
        ], //add_option_to_set_display_event_location_on_event_type
		'add_option_to_set_customizability_of_display_event_location_on_event_type' => [
            'title' => 'Add Option to Set Customizability Of Display Event Location On Event Type',
            'description' => 'Add ability to choose the customizability of including event location to event thumbnail image at the event type level',
            'sql' => [
                "ALTER TABLE event_type ADD COLUMN displayEventBranchOnThumbnailCustomizable TINYINT(1) DEFAULT 0",
            ]
        ], //add_option_to_set_customizability_of_display_event_location_on_event_type
		'add_default_event_calendar_display_dropdown' => [
            'title' => 'Add Default Event Calendar Display Dropdown',
            'description' => 'Add the option of selecting the default display for the native events calendar',
            'continueOnError' => false,
            'sql' => [
                "ALTER TABLE library ADD COLUMN eventsDefaultCalendarView TINYINT(1) NOT NULL DEFAULT 0",
            ],
        ], //add_default_event_calendar_display_dropdown
		'pay360_rename_wsldUrl_to_wsdlUrl' => [
			'title' => 'Rename wsldUrl to wsdlUrl in Pay360 Settings',
			'description' => 'Corrects a Typo in the Column Name (WSLD → WSDL)',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE pay360_setting CHANGE COLUMN wsldUrl wsdlUrl VARCHAR(255)",
			],
		], // pay360_rename_wsldUrl_to_wsdlUrl
		'pay360_drop_identifier_column' => [
			'title' => 'Remove Unused Identifier Column from Pay360 Settings',
			'description' => 'Removes an Unused Column from the pay360_setting Table',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE pay360_setting DROP COLUMN identifier",
			],
		], // pay360_drop_identifier_column
		'pay360_drop_request_parameter_table' => [
			'title' => 'Remove Unused Pay360 Request Parameter Table',
			'description' => 'Drops the pay360_request_parameter Table Which is Not Used by the Pay360 Integration',
			'continueOnError' => true,
			'sql' => [
				"DROP TABLE IF EXISTS pay360_request_parameter",
			],
		], // pay360_drop_request_parameter_table
	];
}