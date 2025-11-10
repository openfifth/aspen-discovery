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
		'populate_location_facet_labels' => [
			'title' => 'Populate Location Facet Labels',
			'description' => 'Copy legacy "location" Translation Map values into the Location table facet labels based on matching codes.',
			'continueOnError' => false,
			'sql' => [
				"UPDATE location
					INNER JOIN translation_maps tm ON tm.name = 'location'
					INNER JOIN translation_map_values tmv ON tmv.translationMapId = tm.id
					SET location.facetLabel = tmv.translation
					WHERE LOWER(location.code) = LOWER(tmv.value)"
			]
		], //populate_location_facet_labels

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		
	];
}
