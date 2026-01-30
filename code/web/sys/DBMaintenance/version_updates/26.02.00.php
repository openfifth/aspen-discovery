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
		'aspen_lida_home_screen_links_permissions' => [
			'title' => 'Aspen LiDA Home Screen Link Permissions',
			'description' => 'Create permissions for managing Aspen LiDA Home Screen Links.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
				('Aspen LiDA', 'Administer All Aspen LiDA Home Screen Links', '', 160, 'Allows the user to manage Aspen LiDA Home Screen Links for all libraries.'),
				('Aspen LiDA', 'Administer Library Aspen LiDA Home Screen Links', '', 161, 'Allows the user to manage Aspen LiDA Home Screen Links for their home library.')
				"
			],
		],
		//aspen_lida_home_screen_links_permissions
		'aspen_lida_home_screen_links_role_permissions' => [
			'title' => 'Aspen LiDA Home Screen Link Role Permission',
			'description' => 'Assign Aspen LiDA Home Screen Link permission to OPAC Admin role.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer All Aspen LiDA Home Screen Links'))",
			],
		],
		//aspen_lida_home_screen_links_role_permissions
		'aspen_lida_home_screen_links_tables' => [
			'title' => 'Aspen LiDA Home Screen Link Settings Tables',
			'description' => 'Create tables to store Aspen LiDA Home Screen Link settings.',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS `aspen_lida_home_screen_link_group` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(50) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
				"CREATE TABLE IF NOT EXISTS `aspen_lida_home_screen_link_group_entry` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`homeScreenLinkGroupId` int(11) NOT NULL,
				`homeScreenLinkId` int(11) NOT NULL,
				`weight` int(11) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`),
				KEY `homeScreenLinkGroupId` (`homeScreenLinkGroupId`),
				KEY `homeScreenLinkId` (`homeScreenLinkId`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
				"CREATE TABLE IF NOT EXISTS `aspen_lida_home_screen_link` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(100) NOT NULL,
				`textId` varchar(100) DEFAULT NULL,
				`userId` int(11) DEFAULT NULL,
				`sharing` enum('everyone','library','private') NOT NULL DEFAULT 'everyone',
				`libraryId` int(11) DEFAULT NULL,
				`typeOfIcon` enum('materialIcon','uploadIcon') NOT NULL DEFAULT 'materialIcon',
				`materialIcon` varchar(100) DEFAULT NULL,
				`uploadIcon` varchar(255) DEFAULT NULL,
				`linkType` enum('deepLink','externalLink') NOT NULL DEFAULT 'deepLink',
				`deepLinkPath` varchar(100) DEFAULT NULL,
				`deepLinkId` varchar(100) DEFAULT NULL,
				`linkUrl` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `userId` (`userId`),
				KEY `libraryId` (`libraryId`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			],
		],
		//aspen_lida_home_screen_links_tables
		'aspen_lida_home_screen_links_group_id_storage' => [
			'title' => 'Aspen LiDA Home Screen Links Group IDs to Libraries and Locations',
			'description' => 'Add columns for Aspen LiDA Home Screen Links Group IDs to Libraries and Locations.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE `library` ADD `lidaHomeScreenLinkGroupId` INT(11) DEFAULT -1;",
				"ALTER TABLE `location` ADD `lidaHomeScreenLinkGroupId` INT(11) DEFAULT -1;",
			],
		],
		//aspen_lida_home_screen_links_group_id_storage

		//kodi

		//yanjun

		//imani
		'add_configuration_for_index_process' => [
			'title' => 'Add configuration for solr soft commits',
			'description' => 'Add additional configuration for how records are processed into solr',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE system_variables ADD COLUMN indexCommitInterval INT DEFAULT 10000",
			]
		],
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
		'offer_immediate_hold_freeze' => [
			'title' => 'Library - Add the Ability to Freeze Holds Immediately',
			'description' => 'Within Library Settings, libraries can choose to offer patrons the ability to freeze their holds immediately.',
			'sql' => [
				"ALTER TABLE library ADD COLUMN offerImmediateHoldFreeze tinyint(1) NOT NULL DEFAULT 0",
			]
		],
		'prompt_to_freeze_holds_immediately' => [
			'title' => 'User - Add the Choice to Have a Prompt to Freeze Holds Immediately',
			'description' => 'Patrons will gain the choice within their Account Settings to have the system prompt them to freeze their holds immediately. (Requires that the library first offers this setting.)',
			'sql' => [
				"ALTER TABLE user ADD COLUMN promptToFreezeHoldsImmediately tinyint(1) NOT NULL DEFAULT 0",
			]
		], //offer_immediate_hold_freeze

		//lucas


		//tomas

		//other


	];
}
