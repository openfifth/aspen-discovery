<?php

/** @noinspection PhpUnused */
function getUpdates25_12_00(): array {
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

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS
		'hide_soft_delete_list_ui' => [
			'title' => 'Hide Soft Delete List UI',
			'description' => 'Add setting to hide soft delete messaging and checkbox when deleting lists',
			'sql' => [
				"ALTER TABLE library ADD COLUMN IF NOT EXISTS hideSoftDeleteListUI TINYINT(1) DEFAULT 0",
			],
		], //hide_soft_delete_list_ui

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		
	];
}
