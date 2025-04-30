<?php

function getUpdates25_06_00(): array {
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

		//mark - Grove

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'add_preferred_name_to_user' => [
            'title' => 'Add Preferred Name To User',
            'description' => 'Add preferred name to user table',
            'continueOnError' => false,
            'sql' => [
                "ALTER TABLE user ADD COLUMN userPreferredName VARCHAR(256) NOT NULL",
            ]
        ], //add_preferred_name

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
