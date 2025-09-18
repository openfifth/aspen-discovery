<?php

/** @noinspection PhpUnused */
function getUpdates25_10_00(): array {
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
		'add_indexes_for_more_user_list_sort_options' => [
			'title' => 'Add Indexes For More User List Sort Options',
			'description' => 'Add indexes idx_publicationDateId and idx_callNumberId for faster User List sorting.',
			'continueOnError' => false,
			'sql' => [
				'CREATE INDEX idx_publicationDateId ON grouped_work_records(publicationDateId)',
				'CREATE INDEX idx_callNumberId ON grouped_work_record_items (callNumberId)'
			],
		], // add_indexes_for_more_user_list_sort_options

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
