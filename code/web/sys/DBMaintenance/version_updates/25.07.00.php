<?php

function getUpdates25_07_00(): array {
	$curTime = time();
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
		'account_profile_enable_fetching_ils_messages' => [
			'title' => 'Add Enable Fetching ILS Messages to Account Profile',
			'description' => 'Add Enable Fetching ILS Messages to Account Profile',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE account_profiles ADD COLUMN enableFetchingIlsMessages TINYINT(1) DEFAULT 0'
			]
		], //account_profile_enable_fetching_ils_messages
		'branded_app_notification_access_token' => [
			'title' => 'Add Notification Access Token To Branded App Settings',
			'description' => 'Add Notification Access Token To Branded App Settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE aspen_lida_branded_settings ADD COLUMN notificationAccessToken varchar(256) DEFAULT NULL',
			]
		], //branded_app_notification_access_token
		'ils_notification_setting_account_profile' => [
			'title' => 'Link ILS Notification Setting to Account Profile',
			'description' => 'Link ILS Notification Setting to Account Profile',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE ils_notification_setting ADD COLUMN accountProfileId INT(11) DEFAULT -1',
				"UPDATE ils_notification_setting SET accountProfileId = (SELECT id from account_profiles where name <> 'admin' and name <> 'admin_sso' LIMIT 1)",
			]
		], //ils_notification_setting_account_profile
		'remove_vendor_specific_defaults' => [
			'title' => 'Remove Vendor Specific Defaults',
			'description' => 'Remove Vendor Specific Default Values',
			'sql' => [
				"ALTER TABLE system_variables CHANGE COLUMN supportingCompany supportingCompany varchar(72) DEFAULT ''",
			]
		], //remove_vendor_specific_defaults

		//katherine - Grove
		'add_series_member_priority_score' => [
			'title' => 'Add a priority score to series member table',
			'description' => 'Add a priority score to series members to sort series prioritizing MARC field 800 over 830',
			'sql' => [
				"ALTER TABLE series_member ADD COLUMN priorityScore TINYINT NOT NULL DEFAULT 1;",
			]
		], //add_series_member_priority_score

		//kirstien - Grove

		//kodi - Grove
		'image_pdf_owning_sharing' => [
			'title' => 'Owning and Sharing for Images and PDFs',
			'description' => 'Add owning and sharing columns to file_uploads and image_uploads.',
			'sql' => [
				"ALTER TABLE file_uploads ADD COLUMN owningLibrary INT(11) NOT NULL DEFAULT -1",
				"ALTER TABLE file_uploads ADD COLUMN sharing INT(11) NOT NULL DEFAULT 2",
				"ALTER TABLE file_uploads ADD COLUMN sharedWithLibrary INT(11) NOT NULL DEFAULT -1",
				"ALTER TABLE image_uploads ADD COLUMN owningLibrary INT(11) NOT NULL DEFAULT -1",
				"ALTER TABLE image_uploads ADD COLUMN sharing INT(11) NOT NULL DEFAULT 2",
				"ALTER TABLE image_uploads ADD COLUMN sharedWithLibrary INT(11) NOT NULL DEFAULT -1",
			],
		], //image_pdf_owning_sharing
		'web_content_permissions' => [
			'title' => 'Web Content Permissions',
			'description' => 'Add restricted (home library only) permissions for web content.',
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('Web Builder', 'Administer Web Content for Home Library', 'Web Builder', 61, 'Allows the user to manage images and pdfs for their home library only.')",
			],
		], //custom_web_resource_pages_roles

		// Myranda - Grove

		//Yanjun Li - ByWater
		'add_comprise_donation_settings' => [
			'title' => 'Add Comprise Donation Settings',
			'description' => 'Add customer name and id for donation in Comprise Settings',
			'sql' => [
				"ALTER TABLE comprise_settings ADD COLUMN customerNameForDonation VARCHAR(50) DEFAULT NULL",
				"ALTER TABLE comprise_settings ADD COLUMN customerIdForDonation INT(11) DEFAULT NULL",
			]
		], //add_comprise_donation_settings

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'add_grapes_templates_to_db' => [
			'title' => 'Add Grapes Temaplates To DB',
			'description' => 'Add Grapes templates to db',
			'sql' => [
				'addTemplateFromJson'
			]
		], //add_grapes_templates_to_db
		'control_display_of_user_dropdown_in_community_engagement_admin_view' => [
			'title' => 'Control User Select Type in Admin View',
			'description' => 'Add options for how to select users in the admin view section',
			'sql' => [
				"ALTER TABLE library ADD COLUMN communityEngagementAdminUserSelect VARCHAR(20) DEFAULT 'dropdown'",
			],
		], //control_display_of_user_dropdown_in_community_engagement_admin_view

		//chloe - Open Fifth
		'move_heycentric_permission' => [
			 'title' => 'Move HeyCentric Permission',
			 'description' => 'Move the Administrer HeyCentric Settings permission into the existing eCommerce section',
			 'continueOnError' => false,
			 'sql' => [
				"UPDATE permissions SET name='Administer HeyCentric', sectionName='eCommerce', description='Allows the user to administer the integration with HeyCentric <em>This has potential security and cost implications.</em>' WHERE name='Administer HeyCentric Settings' AND sectionName='ecommerce'",
			 ],
			 
		 ], // move_heycentric_permission


		//Jacob - Open Fifth
		'sso_do_not_create_user_in_ils' => [
			'title' => 'Do not create SSO user in ils',
			'description' => 'Ability to stop SSO from creating users in the ils',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE sso_setting ADD COLUMN createUserInIls int(11) DEFAULT 1',
			]
		],
		//sso_do_not_create_user_in_ils

		//Jacob - Open Fifth
		'disable_user_agent_logging' => [
			'title' => 'Disable User Agent Logging',
			'description' => 'Add system variable to control user agent logging',
			'sql' => [
				"ALTER TABLE system_variables ADD COLUMN disable_user_agent_logging tinyint(1) DEFAULT 0",
			]
		], //disable_user_agent_logging

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search
		'talpa_settings_defaults_update_07_25' => [
			'title' => 'Update to Talpa Default "Other Results" Explainer Text',
			'description' => 'Updates the default value of talpaOtherResultsExplainerText to clarify results are not owned by the user’s library.',
			'sql' => [
				"ALTER TABLE talpa_settings MODIFY COLUMN talpaOtherResultsExplainerText VARCHAR(180) DEFAULT 'Talpa Search found these other results not owned by your library.'"
			]
		]

	];
}

 function addTemplateFromJson(&$update) {
	require_once ROOT_DIR . '/sys/WebBuilder/GrapesTemplate.php';

	$jsonFile = './web_builder/templates.json';
	if(file_exists($jsonFile)){
		$jsonData = file_get_contents($jsonFile);
		$jsonDecoded = json_decode($jsonData, true);
		$templates = $jsonDecoded['templates'];

		foreach($templates as $preMadeTemplate) {
			$template = new GrapesTemplate();
			$template->addTemplate($preMadeTemplate['templateName'], $preMadeTemplate['templateContent'], $preMadeTemplate['htmlData'] ?? '', $preMadeTemplate['cssData'] ?? '');
		}
		$update['success'] = true;
	}
}