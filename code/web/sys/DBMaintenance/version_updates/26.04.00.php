<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_04_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien


		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//mark j
		'add_pageViewsFromPlacard_to_web_builder_resource_usage' => [
			'title' => 'Track web resource page views from a placard',
			'description' => 'Add new column to keep track of page views from a placard in the Web Builder Resource Usage table.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE web_builder_resource_usage ADD COLUMN pageViewsFromPlacard INT NOT NULL DEFAULT 0',
			]
		], //add_pageViewsFromPlacard_to_web_builder_resource_usage
		'create_placard_usage_table' => [
			'title' => 'Create placard usage table',
			'description' => 'Create placard usage table for tracking things like number of times shown and clicks on placards.',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS placard_usage (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					instance VARCHAR(100),
					year INT,
					month INT,
					placardName VARCHAR(255),
					pageViews INT DEFAULT 0,
					pageViewsByAuthenticatedUsers INT DEFAULT 0,
					pageViewsInLibrary INT DEFAULT 0,
					timesShown INT DEFAULT 0
				) ENGINE=INNODB',
			]
		], //create_placard_usage_table
		'add_index_to_placard_usage' => [
			'title' => 'Add index to placard usage table',
			'description' => 'Add index to placard usage table to improve performance of queries.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE placard_usage ADD UNIQUE INDEX placard_usage_unique (instance, year, month, placardName);",
			]
		], //add_index_to_placard_usage

		//lucas

		//tomas

		// stephen

		//other


	];
}
