<?php

/** @noinspection PhpUnused */
function getUpdates25_10_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove
		'addOptionsForIndexing896To899AsSeries' => [
			'title' => 'Add Options For Indexing 896 To 899 As Series',
			'description' => 'Add Options For Indexing 896 To 899 As Series',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE indexing_profiles ADD COLUMN index896asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index897asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index898asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index899asSeries TINYINT(1) DEFAULT 1'
			]
		], //addOptionsForIndexing896To899AsSeries
		'addHooplaRecordExtractionBatchSize' => [
			'title' => 'Add Hoopla Record Extraction Batch Size',
			'description' => 'Add Hoopla Record Extraction Batch Size',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE hoopla_settings ADD COLUMN recordExtractionBatchSize INT DEFAULT 500',
			]
		], //addHooplaRecordExtractionBatchSize
		'add_permission_for_econtent_sorting' => [
			'title' => 'Add permissions for eContent sorting',
			'description' => 'Add permissions for eContent sorting',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Grouped Work Display', 'Administer All eContent Sorting', '', 60, 'Allows users to change how eContent Sources are sorted within a grouped work for all libraries.')",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Grouped Work Display', 'Administer Library eContent Sorting', '', 70, 'Allows users to change how eContent Sources are sorted within a grouped work for their library.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer All eContent Sorting'))",
			]
		], //add_permission_for_econtent_sorting
		'add_permission_group_for_econtent_sorting' => [
			'title' => 'Add permission group for eContent sorting',
			'description' => 'Add permission group for eContent sorting',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO `permission_groups` (`groupKey`,`sectionName`,`label`,`description`) VALUES
					('adminEContentSorting','Grouped Work Display','Administer eContent Source Sorting','Allows users to change how eContent Sources are sorted within a grouped work.');",
				"INSERT IGNORE INTO `permission_group_permissions` (`groupId`,`permissionId`) SELECT pg.id, p.id FROM `permission_groups` pg JOIN `permissions` p ON p.name IN ('Administer All eContent Sorting','Administer Library eContent Sorting') WHERE pg.groupKey = 'adminEContentSorting'",
			]
		], //add_permission_group_for_econtent_sorting
		'create_econtent_sorting_tables' => [
			'title' => 'Create eContent sorting tables',
			'description' => 'Create eContent sorting tables',
			'continueOnError' => true,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS grouped_work_econtent_sort_group (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL UNIQUE,
					sortAvailableSourcesFirst TINYINT(1) DEFAULT 1,
					sortMethod TINYINT(1) DEFAULT 1
				)',
				'CREATE TABLE IF NOT EXISTS grouped_work_econtent_sort (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					eContentSortingGroupId INT(11) NOT NULL,
					eContentSource VARCHAR(255) NOT NULL,
					weight INT(11) NOT NULL,
					UNIQUE(eContentSortingGroupId, eContentSource)
				)',
			],
		], //create_econtent_sorting_tables
		'create_default_econtent_sorting' => [
			'title' => 'Create default eContent sorting',
			'description' => 'Create default eContent sorting',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO grouped_work_econtent_sort_group (id, name, sortAvailableSourcesFirst, sortMethod) VALUES (1, 'Default', 1, 1)"
			]
		], //create_default_econtent_sorting
		'link_econtent_sorting_to_display_settings' => [
			'title' => 'Link eContent sorting to display settings',
			'description' => 'Link eContent sorting to display settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE grouped_work_display_settings ADD COLUMN eContentSortingGroupId INT(11) DEFAULT 1'
			]
		], //link_econtent_sorting_to_display_settings
		'add_series_sort_method' => [
			'title' => 'Add series sorting method',
			'description' => 'Add series sorting method',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE series ADD COLUMN sortMethod TINYINT DEFAULT 1'
			]
		], //add_series_sort_method

		//katherine - Grove
		'add_include_in_reports_option_to_event_type' => [
			'title' => 'Add Include In Reports option to Event Types',
			'description' => 'Allows specific event types to be excluded from reports',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE event_type ADD COLUMN includeInReports TINYINT DEFAULT 1',
			]
		], //add_include_in_reports_option_to_event_type

		//kirstien - Grove
		'addEditionPromptSettingForLibrary' => [
			'title' => 'Add Option For Prompting For Edition When Placing Hold',
			'description' => 'Add Option For Prompting For Edition When Placing Hold at the Library Level',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN holdPromptForEditions TINYINT DEFAULT 0',
			]
		],
		//addEditionPromptSettingForLibrary
		'addEditionPromptSettingForUser' => [
			'title' => 'Add Options For Storing User Preference on Prompting For Edition When Placing Hold',
			'description' => 'Add Options For Storing User Preference on Prompting For Edition When Placing Hold',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user ADD COLUMN rememberHoldPromptForEdition TINYINT DEFAULT 1',
				'ALTER TABLE user ADD COLUMN holdPromptForEdition TINYINT DEFAULT 1',
			]
		],
		//addEditionPromptSettingForUser

		//kodi - Grove

		// Myranda - Grove
		'add_dark_mode_checkbox' => [
			'title' => 'Add checkbox for if theme is dark mode or not',
			'description' => 'Adds checkbox to themes for additional CSS modifications applicable to dark color schemes',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE themes ADD COLUMN isDarkColorScheme TINYINT(1) DEFAULT 0',
			]
		],
		//add_high_contrast_checkbox

		//Yanjun Li - ByWater
		'add_hoopla_configurable_indexing_time' => [
			'title' => 'Add Configurable Hoopla Indexing Time',
			'description' => 'Add Hoopla Indexing Time',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE hoopla_settings ADD COLUMN indexingTime INT DEFAULT 1',
			]
		], //add_hoopla_configurable_indexing_time

		// Leo Stoyanov - BWS
		'add_num_total_entries_to_show_in_more_to_grouped_work_facet' => [
			'title' => 'Add Total Num Entries To Show In More To Grouped Work Facet',
			'description' => 'Add configurable field to control how many facet values show in the "More..." popup/expansion.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE grouped_work_facet ADD COLUMN numTotalEntriesToShowInMore INT(11) NOT NULL DEFAULT 30',
			]
		], // add_num_total_entries_to_show_in_more_to_grouped_work_facet
		'add_show_copies_for_periodicals_with_no_iems_setting' => [
			'title' => 'Add Show Copies for Periodicals with No Items Setting',
			'description' => 'Add a setting to control whether Copies accordion is shown for periodicals with no items.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE grouped_work_display_settings ADD COLUMN showCopiesForPeriodicalsWithNoItems TINYINT(1) DEFAULT 0'
			]
		], //add_show_copies_for_periodicals_with_no_iems_setting
		'add_enable_third_party_sms_notifications_option' => [
			'title' => 'Add "Enable Third Party SMS Notifications" Option',
			'description' => 'Add "Enable Third Party SMS Notifications" option for CarlX to Library System settings.',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE library ADD COLUMN enableThirdPartySMSNotifications TINYINT(1) DEFAULT 0'
			],
		], // add_enable_third_party_sms_notifications_option
		'remove_request_tracker_tables' => [
			'title' => 'Remove Request Tracker Database Tables',
			'description' => 'Drop all database tables related to the Request Tracker implementation.',
			'continueOnError' => true,
			'sql' => [
				'DROP TABLE IF EXISTS component_ticket_link',
				'DROP TABLE IF EXISTS development_task_ticket_link',
				'DROP TABLE IF EXISTS request_tracker_connection',
				'DROP TABLE IF EXISTS ticket',
				'DROP TABLE IF EXISTS ticket_component_feed',
				'DROP TABLE IF EXISTS ticket_queue_feed',
				'DROP TABLE IF EXISTS ticket_severity_feed',
				'DROP TABLE IF EXISTS ticket_status_feed',
				'DROP TABLE IF EXISTS ticket_trend_bugs_by_severity',
				'DROP TABLE IF EXISTS ticket_trend_by_component',
				'DROP TABLE IF EXISTS ticket_trend_by_partner',
				'DROP TABLE IF EXISTS ticket_trend_by_queue'
			]
		], // remove_request_tracker_tables
		'remove_request_tracker_permissions' => [
			'title' => 'Remove Request Tracker Permissions',
			'description' => 'Remove permissions and role assignments related to the Request Tracker implementation.',
			'continueOnError' => true,
			'sql' => [
				'DELETE FROM role_permissions WHERE permissionId IN (SELECT id FROM permissions WHERE name IN ("Submit Ticket", "Administer Request Tracker Connection", "View Active Tickets", "Set Development Priorities"))',
				'DELETE FROM permissions WHERE name IN ("Submit Ticket", "Administer Request Tracker Connection", "View Active Tickets", "Set Development Priorities")',
				'DROP TABLE IF EXISTS development_priorities'
			]
		], // remove_request_tracker_permissions
		'remove_request_tracker_greenhouse_settings' => [
			'title' => 'Remove Request Tracker Greenhouse Settings',
			'description' => 'Remove Request Tracker fields from greenhouse_settings table.',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE greenhouse_settings DROP COLUMN IF EXISTS requestTrackerBaseUrl',
				'ALTER TABLE greenhouse_settings DROP COLUMN IF EXISTS requestTrackerAuthToken'
			]
		], //remove_request_tracker_greenhouse_settings
		'remove_ticket_email_system_variable' => [
			'title' => 'Remove Ticket Email System Variable',
			'description' => 'Remove ticketEmail column from system_variables table.',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE system_variables DROP COLUMN IF EXISTS ticketEmail'
			]
		], // remove_ticket_email_system_variable

		//alexander - Open Fifth

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search

		// Brendan Lawlor

	];
}
