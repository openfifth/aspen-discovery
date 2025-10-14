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
		'grouped_work_display_settings_showItemBarcodes' => [
			'title' => 'Grouped Work Display Settings - Show Item Barcodes',
			'description' => 'Add option to show item barcodes in copy details.',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN showItemBarcodes TINYINT(1) DEFAULT 0",
			],
		],

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
