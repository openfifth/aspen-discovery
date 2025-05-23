<?php

function getUpdates25_06_00(): array {
	$curTime = time();
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
		'delete_orphaned_series_members' => [
			'title' => 'Delete Orphaned Series Members',
			'description' => 'Delete Series Members that are no longer linked to a valid grouped Work',
			'continueOnError' => false,
			'sql' => [
				'DELETE from series_member where id IN (select series_member.id from series_member left join grouped_work on series_member.groupedWorkPermanentId = permanent_id left join grouped_work_records on grouped_work_records.groupedWorkId = grouped_work.id where grouped_work_records.groupedWorkId IS NULL and userAdded = 0);'
			]
		], //delete_orphaned_series_members
		'correct_default_include_only_holdable_for_records_to_include' => [
			'title' => 'Correct Default Include Only Holdable for Records To Include',
			'description' => 'Correct Default Include Only Holdable for Records To Include',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library_records_to_include CHANGE COLUMN includeHoldableOnly includeHoldableOnly tinyint(1) NOT NULL DEFAULT 0',
				'ALTER TABLE location_records_to_include CHANGE COLUMN includeHoldableOnly includeHoldableOnly tinyint(1) NOT NULL DEFAULT 0'
			]
		], //correct_default_include_only_holdable_for_records_to_include
		'cloud_library_setting_name' => [
			'title' => 'Add CloudLibrary setting name',
			'description' => 'Add a name for CloudLibrary settings',
			'sql' => [
				"ALTER TABLE cloud_library_settings ADD COLUMN name VARCHAR(100) DEFAULT ''",
				"UPDATE cloud_library_settings set name = concat('Setting ', id)",
			]
		], //cloud_library_setting_name
		'indexing_profile_status_alt' => [
			'title' => 'Indexing Profile Status Alt',
			'description' => 'Add the ability to define a second item status field for use while indexing symphony records',
			'sql' => [
				"ALTER TABLE indexing_profiles ADD COLUMN statusAlt CHAR(1) DEFAULT ' '",
				"ALTER TABLE status_map_values ADD COLUMN appliesToStatusSubfield TINYINT(1) DEFAULT 1",
				"ALTER TABLE status_map_values ADD COLUMN appliesToStatusAltSubfield TINYINT(1) DEFAULT 0",
			]
		], //indexing_profile_status_alt
		'improve_urlAlias_performance' => [
			'title' => 'Improve URL Alias Performance',
			'description' => 'Improve URL Alias Performance',
			'sql' => [
				'ALTER TABLE web_builder_basic_page ADD INDEX urlAlias(urlAlias)',
				'ALTER TABLE web_builder_custom_form ADD INDEX urlAlias(urlAlias)',
				'ALTER TABLE web_builder_custom_web_resource_page ADD INDEX urlAlias(urlAlias)',
				'ALTER TABLE web_builder_portal_page ADD INDEX urlAlias(urlAlias)',
				'ALTER TABLE web_builder_quick_poll ADD INDEX urlAlias(urlAlias)',
				'ALTER TABLE grapes_web_builder ADD INDEX urlAlias(urlAlias)',
			]
		], //improve_urlAlias_performance
		'index_optional_update_status' => [
			'title' => 'Index Optional Update Status',
			'description' => 'Index Optional Update Status',
			'sql' => [
				'ALTER TABLE optional_updates ADD INDEX status(status)'
			]
		], //index_optional_update_status
		'improve_location_lookup_performance' => [
			'title' => 'Improve Location Lookup Performance',
			'description' => 'Improve Location Lookup Performance',
			'sql' => [
				'ALTER TABLE location ADD INDEX displayNameLookup(displayName, isMainBranch, libraryId, locationId)',
				'ALTER TABLE location ADD INDEX libraryMainBranch(libraryId, isMainBranch)'
			]
		], //improve_location_lookup_performance
		'improve_translation_term_lookup_performance' => [
			'title' => 'Improve Translation Term Lookup Performance',
			'description' => 'Improve Translation Term Lookup Performance by not using prefix',
			'sql' => [
				'ALTER TABLE translation_terms DROP INDEX term',
				'ALTER TABLE translation_terms ADD INDEX term(term)',
			]
		], //improve_translation_term_lookup_performance
		'improve_language_lookup_performance' => [
			'title' => 'Improve Language Lookup Performance',
			'description' => 'Improve Language Lookup Performance',
			'sql' => [
				'ALTER TABLE languages ADD INDEX languageLookup(weight, displayName)',
			]
		], //improve_location_lookup_performance

		//katherine - Grove
		'add_lida_barcode_entry_keyboard_type_setting' => [
			'title' => 'Add a barcode entry keyboard type field to LiDA self check settings',
			'description' => 'Add keyboard type options to use numeric keyboard, alphanumeric keyboard, or not to allow keyboard entry',
			'sql' => [
				"ALTER TABLE aspen_lida_self_check_settings ADD COLUMN barcodeEntryKeyboardType TINYINT(1) NOT NULL DEFAULT 1;",
			]
		], //add_lida_barcode_entry_keyboard_type_setting
		'run_full_series_index_update' => [
			'title' => 'Run Full Series Index Update',
			'description' => 'Run full Series Index update to clean up deleted series records that are still in the index',
			'sql' => [
				"UPDATE series_indexing_settings SET runFullUpdate = 1;"
			]
		], //run_full_series_index

		//kirstien - Grove
		'last_used_sort_for_user' => [
			'title' => 'Store the last used sort value a patron used for holds and checkouts',
			'description' => 'Store the last used sort value a patron used for holds and checkouts',
			'sql' => [
				"ALTER TABLE user ADD COLUMN holdSortAvailable VARCHAR(75) DEFAULT 'expire'",
				"ALTER TABLE user ADD COLUMN holdSortUnavailable VARCHAR(75) DEFAULT 'title'",
				"ALTER TABLE user ADD COLUMN checkoutSort VARCHAR(75) DEFAULT 'dueDate'"
			],
		], //last_used_sort_for_user

		//kodi - Grove
		'side_loads_library_permissions' => [
			'title' => 'Side Load Home Library Permissions',
			'description' => 'Add permissions for administering side loads and side load scopes based on home library.',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('Cataloging & eContent', 'Administer Side Loads for Home Library', 'Side Loads', 171, 'Allows the user to administer side loads for their home library only.'),
					('Cataloging & eContent', 'Administer Side Load Scopes for Home Library', 'Side Loads', 172, 'Allows the user to administer side load scopes for their home library only.')",
			],
		], //side_loads_library_permissions
		'side_loads_owning_and_sharing' => [
			'title' => 'Side Load Owning and Sharing Library',
			'description' => 'Add owning and sharing library to side loads table.',
			'sql' => [
				"ALTER TABLE sideloads ADD COLUMN owningLibrary INT(11) NOT NULL DEFAULT -1",
				"ALTER TABLE sideloads ADD COLUMN sharing INT(11) NOT NULL DEFAULT 1",
			],
		], //side_loads_owning_and_sharing
		'admin_sticky_filter_table' => [
			'title' => 'Sticky Filter Table',
			'description' => 'Add table for sticky filter options for Admins.',
			'sql' => [
				"DROP TABLE IF EXISTS admin_sticky_filters",
				'CREATE TABLE IF NOT EXISTS admin_sticky_filters (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT(11),
					filterFor VARCHAR(100),
					filterValue VARCHAR(255)
				) ENGINE INNODB',
			],
		], //admin_sticky_filter_table

		//Mark - Grove
		'side_loads_uniqueness' => [
			'title' => 'Side Load Uniqueness',
			'description' => 'Update Uniqueness for Side Loads to be unique based on name and owning library and also add uniqueness for marc path and record url component.',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE sideloads DROP INDEX name",
				"ALTER TABLE sideloads ADD UNIQUE name(name, owningLibrary)",
				"ALTER TABLE sideloads ADD UNIQUE (marcPath)",
				"ALTER TABLE sideloads ADD UNIQUE (recordUrlComponent)",
			],
		], //side_loads_uniqueness

		// Myranda - Grove
		'theme_series_image_explore_more' => [
			 'title' => 'Theme - Add custom image uploads for series results',
			 'description' => 'Update theme table to have a custom image value for series results in explore more.',
			 'sql' => [
				 "ALTER TABLE themes ADD COLUMN seriesImage VARCHAR(100) default ''",
			 ]
		], //theme_series_image_explore_more

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'reading_history_columns_and_index' => [
			'title' => 'Add Force Reading History Load Flag, Reading History Import Start Datetime, & Index',
			'description' => 'Add a flag to force immediate loading of reading history for users, a reading history import start datetime, and an index of initial reading history loaded and the previous two new columns.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user ADD COLUMN IF NOT EXISTS forceReadingHistoryLoad TINYINT(1) DEFAULT 0",
				"ALTER TABLE user ADD COLUMN IF NOT EXISTS readingHistoryImportStartedAt DATETIME DEFAULT NULL",
				"DROP INDEX IF EXISTS idx_reading_history_import_status ON user",
				"CREATE INDEX idx_reading_history_import_status ON user (initialReadingHistoryLoaded, forceReadingHistoryLoad, readingHistoryImportStartedAt)"
			]
		], //reading_history_columns_and_index
		'add_num_regrouped_to_cloudlibrary_extract_logs' => [
			'title' => 'Add numRegrouped Column to CloudLibrary Extract Logs',
			'description' => 'Adds a numRegrouped column to the cloud_library_export_log table to track the number of works regrouped during an extract.',
			'sql' => [
				"ALTER TABLE cloud_library_export_log ADD COLUMN IF NOT EXISTS numRegrouped int(11) DEFAULT 0 AFTER settingId",
			]
		], //add_num_regrouped_to_cloudlibrary_extract_logs
		'increase_size_of_collection_codes_to_exclude' => [
			'title' => 'Increase the Size of the collectionCodesToExclude Column',
			'description' => 'Increases the size of the collectionCodesToExclude column from VARCHAR(100) to VARCHAR(500).',
			'sql' => [
				"ALTER TABLE `library_records_to_include` MODIFY COLUMN `collectionCodesToExclude` varchar(500) NOT NULL DEFAULT ''",
				"ALTER TABLE `location_records_to_include` MODIFY COLUMN `collectionCodesToExclude` varchar(500) NOT NULL DEFAULT ''"
			]
		], //increase_size_of_collection_codes_to_exclude
		'add_static_location_id_to_portal_cell' => [
			'title' => 'Add staticLocationId Column to Web Builder Portal Cells',
			'description' => 'Adds a staticLocationId column to the web_builder_portal_cell table to represent the location ID of the static location chosen.',
			'sql' => [
				"ALTER TABLE web_builder_portal_cell ADD COLUMN IF NOT EXISTS staticLocationId int(11) NOT NULL DEFAULT -1",
			]
		], //add_static_location_id_to_portal_cell
		'curbside_pickups_overhaul_05_2025' => [
			'title' => 'Modifications to the Curbside Pickup Settings Table',
			'description' => 'Drops the unused alwaysAllowPickups column and change the default of timeAllowedBeforeCheckIn to -1.',
			'sql' => [
				'ALTER TABLE curbside_pickup_settings DROP COLUMN IF EXISTS alwaysAllowPickups',
				'ALTER TABLE curbside_pickup_settings MODIFY COLUMN timeAllowedBeforeCheckIn int(11) DEFAULT -1'
			]
		], //curbside_pickups_overhaul_05_2025
		'add_prioritize_available_records_for_title_selection' => [
			'title' => 'Add prioritizeAvailableRecordsForTitleSelection to the Indexing Profile',
			'description' => 'Adds a setting to prioritize available records when selecting titles for display in grouped works (Koha only).',
			'sql' => [
				"ALTER TABLE indexing_profiles DROP COLUMN IF EXISTS ignoreOnOrderRecordsForTitleSelection",
				"ALTER TABLE indexing_profiles ADD COLUMN IF NOT EXISTS prioritizeAvailableRecordsForTitleSelection TINYINT(1) DEFAULT 0"
			],
		], // add_prioritize_available_records_for_title_selection

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'update_award_reward_automatically_to_false_by_default' => [
			'title' => 'Update Award Reward Automaticaly to False By Default',
			'description' => 'Update default for award automatically to false',
			'sql' => [
				"ALTER TABLE ce_reward ALTER awardAutomatically SET DEFAULT 0",
			]
		], //update_award_reward_automatically_to_false_by_default
		'add_preferred_name_to_user' => [
			'title' => 'Add Preferred Name To User',
			'description' => 'Add preferred name to user table',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user ADD COLUMN userPreferredName VARCHAR(256) NOT NULL",
			]
		], //add_preferred_name
		'add_preferred_name_option_to_dropdown' => [
			'title' => 'Add Preferred Name Option To Dropdown',
			'description' => 'Add the preferred name option to the name display dropdown in the library.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library MODIFY COLUMN patronNameDisplayStyle ENUM('firstinitial_lastname','lastinitial_firstname','firstinitial_middleinitial_lastname','firstname_middleinitial_lastinitial', 'preferredname_lastinitial') DEFAULT 'firstinitial_lastname'",
			]
		], //add_preferred_name_option_to_dropdown
		'allow_replacement_of_all_instances_of_first_name' => [
			'title' => 'Allow Replacement Of All Instances Of First Name',
			'description' => 'Allow replacement of all instances of first name with the patron\'s preferred name from the ILS id set',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN replaceAllFirstNameWithPreferredName TINYINT(1) DEFAULT 0",
			]
		], //allow_replacement_of_all_instances_of_first_name
		'add_admin_control_over_campaign_leaderboard' => [
			'title' => 'Add Admin Control Over Campaign Leaderboard',
			'description' => 'Add ability for admin to control whether leaderbaord displays',
			'sql' => [
				"ALTER TABLE library ADD COLUMN displayCampaignLeaderboard TINYINT(1) DEFAULT 0",
			]
		], //add_admin_control_over_campaign_leaderboard

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}