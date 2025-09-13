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

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

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
