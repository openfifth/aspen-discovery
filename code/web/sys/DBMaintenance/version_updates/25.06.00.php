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

		//Mark - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'update_award_reward_automatically_to_false_by_default' => [
			'title' => 'Update Award Reward Automaticaly to False By Default',
			'description' => 'Update default for award automatically to false',
			'sql' => [
				"ALTER TABLE ce_reward ALTER awardAutomatically SET DEFAULT 0",
			]
		], //update_award_reward_automatically_to_false_by_default

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
