<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_06_00(): array {
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

		//pedro

		//mark j
		'load_libraries_and_locations_from_ils' => [
				'title' => 'Add "load libraries and locations from ILS" to the indexing_profiles table',
				'description' => 'Adds a checkbox to control whether library/location data is imported from the ILS during Polaris export',
				'continueOnError' => false,
				'sql' => [
						"ALTER TABLE indexing_profiles ADD COLUMN loadLibrariesAndLocationsFromIls TINYINT(1) NOT NULL DEFAULT 1"
				]
		], //load_libraries_and_locations_from_ils

		//lucas

		//tomas

		// stephen

		//other

	];
}
