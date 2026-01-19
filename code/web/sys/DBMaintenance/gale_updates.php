<?php
/**@noinspection SqlResolve*/
function getGaleUpdates() {
	return [
		'gale_module' => [
			'title' => 'Gale Module',
			'description' => 'Create Gale Module',
			'sql' => [
				"INSERT INTO modules (name, indexName, backgroundProcess) VALUES ('Gale', '', '')",
			],
		],
		'create_gale_settings' => [
			'title' => 'Create Gale Settings',
			'description' => 'Add table for gale settings',
			'sql' => [
				"CREATE TABLE gale_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL,
					locationId VARCHAR(255) NOT NULL
				) ENGINE = InnoDB",
			],
		],
		'add_gale_settings_to_library' => [
			'title' => 'Add Gale Settings to Library',
			'description' => 'Add column to library table to store gale settings',
			'sql' => [
				'ALTER TABLE library ADD COLUMN galeSettingsId INT(11) DEFAULT -1',
			],
		],
		'add_permissions_for_gale_module_2' => [
			'title' => 'Add Permissions For Gale Module',
			'description' => 'Add permissions for the Gale Module',
			'sql' => [
				"INSERT INTO permissions (id, name, sectionName, requiredModule, weight, description) VALUES (253, 'Administer Gale', 'Cataloging & eContent', 'Gale', 125, 'Allows the user to administer Gale integration for all libraries.');",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Gale'))",
                "INSERT INTO permissions (id, name, sectionName, requiredModule, weight, description) VALUES (263, 'Library Gale Options', 'Primary Configuration - Library Fields', '', 49, 'Configure Library fields related to Gale content.');",
			],
		],
		'create_gale_product_codes' => [
			'title' => 'Create Gale Product Codes Table',
			'description' => 'Add table for Gale product code display names',
			'sql' => [
				"CREATE TABLE gale_product_codes (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					settingId INT NOT NULL,
					productCode VARCHAR(50) NOT NULL,
					displayName VARCHAR(255) NOT NULL,
					INDEX (settingId)
				) ENGINE = InnoDB",
			],
		],
	];
}
