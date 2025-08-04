<?php

function getUpdates25_09_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth
		'increase_location_display_name_allowed_length' => [
			'title' => 'Increase Location Display Name Allowed Length',
			'description' => 'Increase the allowed length for the location display name',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE location MODIFY displayName VARCHAR(100) NOT NULL'
			],
		], // increase_location_display_name_allowed_length

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search
		
	];
}
