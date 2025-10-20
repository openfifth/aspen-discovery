<?php

/** @noinspection PhpUnused */
function getUpdates25_11_00(): array {
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

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'add_hide_manifestations_in_mobile_view_setting' => [
			'title' => 'Add Hide Manifestations in Mobile View Setting',
			'description' => 'Allow libraries to control whether grouped work formats are condensed on mobile devices.',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE grouped_work_display_settings ADD COLUMN IF NOT EXISTS hideManifestationsInMobileView TINYINT(1) DEFAULT 1'
			]
		], // add_hide_manifestations_in_mobile_view_setting

		//alexander - Open Fifth

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search

		// Brendan Lawlor

	];
}
