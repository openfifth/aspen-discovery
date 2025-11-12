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
		'list_format_filter_persistence' => [
			'title' => 'Allow Persistence of User List Format Filters',
			'description' => 'Add userListFilters column to persist format filters per list for users.',
			'sql' => [
				'ALTER TABLE user_page_defaults ADD COLUMN IF NOT EXISTS userListFilters VARCHAR(512) DEFAULT NULL'
			]
		], //list_format_filter_persistence

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		
	];
}
