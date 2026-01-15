<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_02_00(): array {
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

		//kodi

		//yanjun

		//imani

		//galen

		//alexander
		'add_admin_view_permission_to_community_engagement' => [
			'title' => 'Add Admin View Permission to Community Engagement',
			'description' => 'Add a new permission for admin view page for commnuity engagement',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Community Engagement', 'View Community Engagement Admin View', 'Community Engagement', 200, 'Allows the user to view the Community Engagement Admin View.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES 
					((SELECT roleId FROM roles WHERE name='opacAdmin'), 
						(SELECT id FROM permissions WHERE name='View Community Engagement Admin View'))"
			]
		],// add_admin_view_permission_to_community_engagement
		'community_engagement_section_rename' => [
			'title' => 'Community Engagement - Move Permissions to Community Engagement Section',
			'description' => 'Updates the sectionName for Community Engagement permissions from their current sections to Community Engagement',
			'continueOnError' => false,
			'sql' => [
				"UPDATE permissions SET sectionName = 'Community Engagement' WHERE name = 'View Community Engagement Dashboard'",
				"UPDATE permissions SET sectionName = 'Community Engagement' WHERE name = 'Administer Community Engagement Module'"
			]
		], //community_engagement_section_rename

		//chloe

		//mark j

		//lucas


		//tomas

		//other


	];
}
