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

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'grouped_work_display_settings_numSeriesToShowBeforeMore' => [
			'title' => 'Grouped Work Display Settings - Add Number of Series to Show Before "More Series" Link',
			'description' => 'Add numSeriesToShowBeforeMore field to grouped_work_display_settings table to configure the number of series entries displayed before showing "More Series..." link.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS numSeriesToShowBeforeMore INT(11) DEFAULT 3",
			]
		],

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		
	];
}
