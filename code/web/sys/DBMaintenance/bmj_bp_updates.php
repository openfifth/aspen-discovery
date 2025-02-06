<?php
/** @noinspection SqlResolve */
function getBmjBpUpdates() {
	return [
		'create_bmj_bp_module' => [
			'title' => 'Create BMJ Best Practice module',
			'description' => 'Setup modules for BMJ Best Practice Integration',
			'sql' => [
				"INSERT INTO modules (name, indexName, backgroundProcess) VALUES ('BMJ Best Practice', '', '')",

			],
		],
		'create_settings_for_bmj_bp' => [
			'title' => 'Create BMJ Best Practice settings',
			'description' => 'Create settings to store information for BMJ Best Practice Integrations',
			'continueOnError' => true,
			'sql' => [
				"CREATE TABLE bmj_bp_settings (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(250) NOT NULL,
					bmjBpBaseApiUrl VARCHAR(250) DEFAULT '',
					bmjBpApiKey VARCHAR(250) DEFAULT '',
					bmjBpApiSecret VARCHAR(250) DEFAULT ''
				)",
				'ALTER TABLE library ADD COLUMN bmjBpSettingId INT(11) DEFAULT -1',
			],
		],
	];
}