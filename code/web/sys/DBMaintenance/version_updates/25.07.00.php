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
		'sso_do_not_create_user_in_ils' => [
			'title' => 'Do not create SSO user in ils',
			'description' => 'Ability to stop SSO from creating users in the ils',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE sso_setting ADD COLUMN createUserInIls int(11) DEFAULT 1',
			]
		],
		//sso_do_not_create_user_in_ils

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
