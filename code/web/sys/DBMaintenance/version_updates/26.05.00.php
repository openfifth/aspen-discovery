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

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
