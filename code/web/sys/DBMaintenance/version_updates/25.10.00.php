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

		//katherine - Grove

		//kirstien - Grove
		'addEditionPromptSettingForLocation' => [
			'title' => 'Add Option For Prompting For Edition When Placing Hold',
			'description' => 'Add Option For Prompting For Edition When Placing Hold at the Location Level',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE location ADD COLUMN holdPromptForEditions INT DEFAULT 0',
			]
		],
		//addEditionPromptSettingForLocation

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
