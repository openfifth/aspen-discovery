<?php

/** @noinspection PhpUnused */
function getUpdates25_11_00(): array {
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

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'indexing_profile_displayTitleStripRegex' => [
			'title' => 'Indexing Profile - Display Title Strip Regex',
			'description' => 'Add regex field to the Indexing Profile to strip text from display titles of ILS records.',
			'sql' => [
				'ALTER TABLE indexing_profiles ADD COLUMN IF NOT EXISTS displayTitleStripRegex TEXT'
			]
		], //indexing_profile_displayTitleStripRegex

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
