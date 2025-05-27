<?php

function getUpdates25_07_00(): array {
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

		//Mark - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth

		//chloe - Open Fifth

		//Jacob - Open Fifth
		'disable_user_agent_logging' => [
			'title' => 'Disable User Agent Logging',
			'description' => 'Add system variable to control user agent logging',
			'sql' => [
				"ALTER TABLE system_variables ADD COLUMN disable_user_agent_logging tinyint(1) DEFAULT 0",
			]
		], //disable_user_agent_logging

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
