<?php

function getUpdates25_06_01(): array {
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
		'add_default_value_to_user_preferred_name' => [
			'title' => 'Add Default Value to User Preferred Name',
			'description' => 'Add Default Value to User Preferred Name',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user CHANGE COLUMN userPreferredName userPreferredName VARCHAR(256) NOT NULL DEFAULT ''",
			]
		], //add_default_value_to_user_preferred_name

		//katherine

		//kirstien - Grove

		//sublocation_ptype_uniqueness

		//kodi

		// Leo Stoyanov - BWS

		//alexander - PTFS-Europe

		//chloe - PTFS-Europe

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//yanjun - ByWater


	];
}
