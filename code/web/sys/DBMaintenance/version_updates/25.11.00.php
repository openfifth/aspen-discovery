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
		'add_theme_soft_delete_columns' => [
			'title' => 'Add Soft Delete Columns to Themes',
			'description' => 'Add metadata needed to support Object Restorations for Themes.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS deleted TINYINT(1) DEFAULT 0",
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS dateDeleted INT(11) DEFAULT 0",
				"ALTER TABLE themes ADD COLUMN IF NOT EXISTS deletedBy INT(11) DEFAULT NULL",
			],
		], //add_theme_soft_delete_columns

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
