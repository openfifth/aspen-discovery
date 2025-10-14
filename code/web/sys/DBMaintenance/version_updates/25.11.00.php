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

		//alexander - Open Fifth
		'add_ability_for_admin_to_control_whether_holds_can_be_grouped' => [
			'title' => 'Add Ability for Admin to Control Whether Holds Can Be Grouped',
			'description' => 'Allow admin to control whether holds can be grouped',
			'sql' => [
				"ALTER TABLE library ADD COLUMN allowHoldsToBeGrouped TINYINT(1) DEFAULT 0",
			],
		], //add_ability_for_admin_to_control_whether_holds_can_be_grouped

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
