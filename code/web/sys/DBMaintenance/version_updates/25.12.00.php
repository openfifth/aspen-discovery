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
		'library_options_to_disable_pickup_locations' => [
			'title' => 'Library Options to Disable Pickup Locations',
			'description' => 'Add options to library settings to allow disabling pickup locations',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN hidePickupLocationPrompt TINYINT(1) DEFAULT 0',
				'ALTER TABLE library ADD COLUMN allowChangingPickupLocationForUnavailableHolds TINYINT(1) DEFAULT 1',
			]
		], //library_options_to_disable_pickup_locations
		'increase_hoopla_status_field' => [
			'title' => 'Increase Hoopla Status Field Length',
			'description' => 'Increase Hooopla Status Field Length',
			'sql' => [
				'ALTER TABLE hoopla_flex_availability CHANGE COLUMN status status VARCHAR(20) NOT NULL'
			]
		], //increase_hoopla_status_field
		'search_interpreter_remove_custom_facets' => [
			'title' => 'Remove Custom Facets from Search Interpreter',
			'description' => 'Remove Custom Facets from Search Interpreter in favor of special terms',
			'sql' => [
				'ALTER TABLE search_interpreter_settings DROP COLUMN audienceFacet',
				'ALTER TABLE search_interpreter_settings DROP COLUMN fictionNonFictionFacet'
			]
		], //search_interpreter_remove_custom_facets
		
		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater
		'add_hoopla_version_to_system_variables' => [
			'title' => 'Add Hoopla Version to System Variables',
			'description' => 'Add Hoopla Version to System Variables',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN hooplaVersion INT DEFAULT 1',
			]
		], //add_hoopla_version_to_system_variables

		// Leo Stoyanov - BWS

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
