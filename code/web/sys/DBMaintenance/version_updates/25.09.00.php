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
		'add_title_to_user_work_review' => [
			'title' => 'Add Title To user Work Review',
			'description' => 'Add title of reviewed work to table',
			'sql' => [
				"ALTER TABLE user_work_review ADD COLUMN title VARCHAR(512) DEFAULT ''",
			]
		], //add_title_to_user_work_review

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search
		
	];
}
