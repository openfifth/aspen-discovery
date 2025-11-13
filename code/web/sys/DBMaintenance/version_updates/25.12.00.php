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
	
		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth
		'add_admin_view_permission_to_community_engagement' => [
			'title' => 'Add Admin View Permission to Community Engagement',
			'description' => 'Add a new permission for admin view page for commnuity engagement',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Primary Configuration', 'View Community Engagement Admin View', 'Community Engagement', 200, 'Allows the user to view the Community Engagement Admin View.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES 
					((SELECT roleId FROM roles WHERE name='opacAdmin'), 
						(SELECT id FROM permissions WHERE name='View Community Engagement Admin View'))"
			]
		],// add_admin_view_permission_to_community_engagement

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
