<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_05_00(): array {
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
		'municipality_extend_registration' => [
			'title' => 'Allow Extending Registration In Sierra Municipality',
			'description' => 'Convert array to traditional syntax',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE self_reg_municipality_values_sierra ADD COLUMN extendExpirationToMonthEnd TINYINT(1) DEFAULT 0',
			]
		], //municipality_extend_registration
		'create_plugin_table' => [
			'title' => 'Create Plugin Table',
			'description' => 'Create the plugin table for storing plugin information and configuration',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE plugin (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL,
					version VARCHAR(20) NOT NULL,
					description TEXT,
					author VARCHAR(100),
					enabled TINYINT(1) NOT NULL DEFAULT 1,
					updateDate INT(11),
					minAspenVersion VARCHAR(20) COMMENT 'Minimum required Aspen Discovery version',
					INDEX idx_plugin_slug (name, enabled)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
			]
		], //create_plugin_table
		'create_plugin_permission' => [
			'title' => 'Create Plugin Administration Permission',
			'description' => 'Add permission to administer plugins',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('System Administration', 'Administer Plugins', '', 80, 'Controls if the user can administer plugins.')"
			]
		], //create_plugin_permission
		'add_html_body_to_email_templates' => [
			'title' => 'Add HTML Body to Email Templates',
			'description' => 'Add HTML Body to Email Templates',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE email_template ADD COLUMN htmlBody TEXT"
			]
		], //add_html_body_to_email_templates
		'setup_default_saved_search_email_template' => [
			'title' => 'Setup Default Saved Search Email Template',
			'description' => 'Add permission to administer plugins',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO email_template (name, templateType, languageCode, subject, plainTextBody, htmlBody) VALUES ('Default Saved Search Alert', 'savedSearchAlert', 'en', 'New Library Materials Match Your Saved Searches', 'The library has added new materials to its collection that may be of interest based on your saved searches (%searchHistory.url%). You may view and request the material via the link(s) below.\r\n\r\n%searchHistory.updatedSearchesWithSampleTitles%', '<p>The library has added new materials to its collection that may be of interest based on your <a href=\'%searchHistory.url%\'>saved searches</a>. You may view and request the material via the link(s) below.</p><div>%searchHistory.updatedSearchesWithSampleTitlesHtml%</div>')"
			]
		], //create_plugin_permission
		'self_check_completion_message_name' => [
			'title' => 'Add a name to Self Check Completion Message',
			'description' => 'Add permission to administer plugins',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE self_check_completion_message ADD COLUMN name TEXT"
			]
		], //self_check_completion_message_name

		//kirstien

		//kodi
		'indexed_duration' => [
			'title' => 'Add indexed_duration Table',
			'description' => 'Add table for indexing duration of grouped work variations (audiobooks).',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS indexed_duration  (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					duration int(11)
				) ENGINE = InnoDB',
			]
		], //indexed_duration
		'indexed_duration_id' => [
			'title' => 'Add durationId Column',
			'description' => 'Add durationId column to grouped_work_records.',
			'sql' => [
				'ALTER TABLE grouped_work_records ADD COLUMN durationId int(11)'
			]
		], // indexed_duration_id

		//yanjun
		'migrate_old_mpaa_rating_to_content_rating' => [
			'title' => 'Migrate old mpaa_rating to content_rating',
			'description' => 'Migrate old mpaa_rating to content_rating',
			'sql' => [
				"UPDATE grouped_work_facet SET facetName = 'content_rating', displayName = 'Content Rating', displayNamePlural = 'Content Ratings' WHERE facetName = 'mpaa_rating';",
			],
		], //migrate_old_mpaa_rating_to_content_rating

		//imani
		// Aspen Progressive Web Application(PWA) updates moved
		'create_aspen_pwa_module' => [
			'title' => 'Create Aspen Progressive Web Application(PWA) Module',
			'description' => 'Setup Aspen Progressive Web Application(PWA) (Progressive Web Application) module',
			'sql' => [
				"INSERT IGNORE INTO modules (name, indexName, backgroundProcess) VALUES ('Aspen Progressive Web Application(PWA)', '', '')",
			],
		],
		'create_aspen_pwa_settings' => [
			'title' => 'Create Aspen Progressive Web Application(PWA) Settings',
			'description' => 'Create database table for Aspen Progressive Web Application(PWA) settings',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS aspen_pwa_settings (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name varchar(50) NOT NULL,
					shortName varchar(50) NOT NULL,
					description varchar(200) NOT NULL,
					themeId int(11) NOT NULL,
					manifestID varchar(50) NOT NULL,
					startURL  varchar(50) DEFAULT '/',
					slug  varchar(50) NOT NULL,
					sha256CertFingerprint  varchar(200) NOT NULL,
					firebaseAPIKey varchar(50) NOT NULL,
					firebaseAuthDomain varchar(50) NOT NULL,
					firebaseProjectID varchar(50) NOT NULL,
					firebaseStorageBucket varchar(50) NOT NULL,
					firebaseMessagingSenderID varchar(50) NOT NULL,
					firebaseAppID varchar(50) NOT NULL,
					vapidKey varchar(100) NOT NULL,
					serviceAccount varchar(5000) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
			]
			],
		'alter_user_notification_token' => [
			'title' => 'User Notification Token Update',
			'description' => 'Adding tokenType field to user notification Token',
			'sql' => [
				"SELECT count(*)
					INTO @exist
					FROM information_schema.columns 
					WHERE table_schema = database()
					and COLUMN_NAME = 'tokenType'
					AND table_name = 'user_notification_tokens';

					set @query = IF(@exist <= 0, 'alter table user_notification_tokens add column tokenType varchar(16) default \'expo\'', 'select \'Column Exists\' status');

					prepare stmt from @query;

					EXECUTE stmt;
					DEALLOCATE PREPARE stmt;",
			],
		],
		'alter_library_add_setting' => [
			'title' => 'Add Aspen Progressive Web Application(PWA) Setting Id',
			'description' => 'update library to include Aspen Progressive Web Application(PWA) setting ID to link to Aspen Progressive Web Application(PWA) settings',
			'sql' => [
				"ALTER TABLE library add column `AspenPWASettingId` int(11) Default -1;"
			],
		],
		'insert_aspen_pwa_permissions' => [
			'title' => 'Add Aspen Progressive Web Application(PWA) permissions',
			'description' => 'Add permisions for administering Aspen Progressive Web Application(PWA) and sending notifications',
			'sql' => [
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Administer Aspen Progressive Web Application(PWA) Settings','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 10, 'Controls if the user can change Aspen Progressive Web Application(PWA) Settings.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to All Libraries','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from all libraries.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to All Locations','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from all locations.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to Home Library','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from their home library.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen Progressive Web Application(PWA) Notifications to Home Location','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users from their home location.');",
				"INSERT IGNORE into `permissions` (name, sectionName, requiredModule, weight, description) VALUES ('Send Aspen PWA Notifications to Home Library Locations','Aspen Progressive Web Application(PWA)', 'Aspen Progressive Web Application(PWA)', 6, 'Controls if the user can send notifications to Aspen Progressive Web Application(PWA) users for all locations that are part of their home library.');",
			],
		],
		//galen

		//chloe

		//pedro

		//mark j
		'user_agent_consolidation' => [
			'title' => 'Consolidate User Agents and Stats',
			'description' => 'Consolidating user agents and their corresponding stats to remove duplicates that only differ by version details. This will allow for cleaner reporting and bot detection.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE user_agent DROP INDEX userAgent, ADD INDEX userAgent (userAgent(512))",
				"UPDATE user_agent SET userAgent = SUBSTRING_INDEX(userAgent, '/', 1) WHERE userAgent LIKE '%/%'",
				"CREATE TABLE user_agent_temp LIKE user_agent",
				"INSERT INTO user_agent_temp (userAgent, isBot, blockAccess)
				 SELECT userAgent,
						MAX(isBot),
						MAX(blockAccess)
				 FROM user_agent
				 GROUP BY userAgent",
				"CREATE TABLE usage_by_user_agent_temp LIKE usage_by_user_agent",
				"INSERT INTO usage_by_user_agent_temp (userAgentId, year, month, instance, numRequests, numBlockedRequests)
				 SELECT consolidated_user_agent.id,
						usage_by_user_agent.year,
						usage_by_user_agent.month,
						usage_by_user_agent.instance,
						SUM(usage_by_user_agent.numRequests),
						SUM(usage_by_user_agent.numBlockedRequests)
				 FROM usage_by_user_agent
				 INNER JOIN user_agent original_user_agent ON usage_by_user_agent.userAgentId = original_user_agent.id
				 INNER JOIN user_agent_temp consolidated_user_agent ON consolidated_user_agent.userAgent <=> original_user_agent.userAgent
				 GROUP BY consolidated_user_agent.id, usage_by_user_agent.year, usage_by_user_agent.month, usage_by_user_agent.instance",
				"DROP TABLE usage_by_user_agent",
				"RENAME TABLE usage_by_user_agent_temp TO usage_by_user_agent",
				"DROP TABLE user_agent",
				"RENAME TABLE user_agent_temp TO user_agent",
				"ALTER TABLE user_agent DROP INDEX userAgent, ADD UNIQUE INDEX userAgent (userAgent(512))"
			]
		], //user_agent_consolidation
		'web_resource_show_in_explore_more' => [
			'title' => 'Add Option to Web Resources to Show in Explore More',
			'description' => 'Add option in web builder resource settings to show in explore more',
			'sql' => [
				"ALTER TABLE web_builder_resource ADD COLUMN showInExploreMore TINYINT(1) DEFAULT 1"
			]
		], //web_resource_show_in_explore_more

		//lucas

		//tomas

		// stephen


		//pedro

		//other
		'remove_site_active_ticket_feed' => [
			 'title' => 'Remove Active Ticket Feed',
			 'description' => 'Deletes the Active Ticket Feed field from the Greenhouse Site List settings.',
			 'continueOnError' => false,
			 'sql' => [
				 'ALTER TABLE aspen_sites DROP COLUMN IF EXISTS activeTicketFeed'
			 ]
		 ], //remove_site_active_ticket_feed
	];
}
