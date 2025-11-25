<?php

/** @noinspection PhpUnused */
function getUpdates25_12_00(): array {
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
		'library_options_to_disable_pickup_locations' => [
			'title' => 'Library Options to Disable Pickup Locations',
			'description' => 'Add options to library settings to allow disabling pickup locations',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN hidePickupLocationPrompt TINYINT(1) DEFAULT 0',
				'ALTER TABLE library ADD COLUMN allowChangingPickupLocationForUnavailableHolds TINYINT(1) DEFAULT 1',
			]
		], //library_options_to_disable_pickup_locations
		
		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'grouped_work_display_settings_showIndexedSeriesWithNoveList' => [
			'title' => 'Grouped Work Display Settings - Add Show Indexed Series with NoveList',
			'description' => 'Add showIndexedSeriesWithNoveList field to grouped_work_display_settings table to control whether indexed series are displayed alongside NoveList or manual override series.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS showIndexedSeriesWithNoveList TINYINT(1) DEFAULT 0",
			]
		],
		'grouped_work_display_settings_numSeriesToShowBeforeMore' => [
			'title' => 'Grouped Work Display Settings - Add Number of Series to Show Before "More Series" Link',
			'description' => 'Add numSeriesToShowBeforeMore field to grouped_work_display_settings table to configure the number of series entries displayed before showing "More Series..." link.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS numSeriesToShowBeforeMore INT(11) DEFAULT 3",
			]
		],
		'grouped_work_display_settings_hideIndexedEContentSeries' => [
			'title' => 'Grouped Work Display Settings - Add Hide Indexed E-Content Series',
			'description' => 'Add hideIndexedEContentSeries field to grouped_work_display_settings table to control whether indexed series from eContent sources (OverDrive, Hoopla) are hidden from display.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS hideIndexedEContentSeries TINYINT(1) DEFAULT 0",
			]
		],

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
