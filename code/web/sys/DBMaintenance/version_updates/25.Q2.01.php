<?php

function getUpdates25_Q2_01(): array {
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

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'add_placard_image_max_height_to_themes' => [
			'title' => 'Add Placard Image Max Height to Themes',
			'description' => 'Adds a placardImageMaxHeight column to the themes table to control placard image height.',
			'sql' => [
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS `placardImageMaxHeight` INT DEFAULT 0",
			]
		], //add_placard_image_max_height_to_themes

		//alexander - PTFS-Europe
		'add_weight_to_campaign_milestones' => [
			'title' => 'Add Weight To Campaign Milestones',
			'description' => 'Add a weight column to campaign milestones to allow ordering',
			'sql' => [
				"ALTER TABLE ce_campaign_milestones ADD COLUMN weight int(11) NOT NULL DEFAULT 0",
			],
		], //DIS-606

		//chloe - PTFS-Europe

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
