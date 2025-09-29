<?php

function getUpdates25_09_01(): array {
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
		'increase_object_history_property_name_length' => [
			'title' => 'Increase the Length of the Object History Property Name',
			'description' => 'Increase the Length of the Object History Property Name',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE object_history CHANGE COLUMN propertyName propertyName VARCHAR(256) NOT NULL",
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
