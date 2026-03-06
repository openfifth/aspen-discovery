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

		//lucas

		//tomas
		'custom_grouped_work_search_specs' => [
			'title' => 'Custom Grouped Work Search Specs',
			'description' => 'Add customGroupedWorkSearchSpecs setting to library table for library-specific grouped work search specs configuration',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN IF NOT EXISTS customGroupedWorkSearchSpecs TEXT DEFAULT NULL COMMENT "Path to custom grouped work search specs YAML file" AFTER casContext'
			]
		], //custom_grouped_work_search_specs

		// stephen

		//other


	];
}
