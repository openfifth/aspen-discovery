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

		//katherine - Grove

		//kirstien - Grove

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
