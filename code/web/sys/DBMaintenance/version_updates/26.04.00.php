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
		'include_econtent_in_shelf_locations_facet' => [
			'title' => 'Add Setting for Including/Excluding eContent in Shelf Locations Facet',
			'description' => 'Add Setting for Including/Excluding eContent in Shelf Locations Facet',
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN includeEContentInShelvingLocations TINYINT(1) DEFAULT 1"
			]
		], //include_econtent_in_shelf_locations_facet

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
