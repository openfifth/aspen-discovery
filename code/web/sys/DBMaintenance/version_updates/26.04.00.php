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
		'bypass_aspen_cloudsource_page' => [
			'title' => 'Add Option to Bypass Aspen CloudSource Record Page',
			'description' => 'Add option in cloudsource settings to bypass aspen cloudsource record pages',
			'sql' => [
				"ALTER TABLE cloudsource_setting ADD COLUMN bypassAspenCloudSourcePage TINYINT(1) DEFAULT 0"
			]
		], //bypass_aspen_cloudsource_page

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

		//lucas

		//tomas

		// stephen

		//other


	];
}
