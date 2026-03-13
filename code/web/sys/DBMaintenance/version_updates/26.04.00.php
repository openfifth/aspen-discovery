<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_04_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien
		'list_transfer_permission' => [
			'title' => 'Add list transfer permission',
			'description' => 'Create permission for allowing transfer of list ownership.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('User Lists', 'Transfer Lists', '', 6, 'Allows the user to transfer a list to another staff.')
				",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Transfer Lists'))",
			],
		],
		//list_transfer_permission

		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//mark j

		//lucas

		//tomas

		// stephen

		//other


	];
}
