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

		//kirstien

		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j
		'create_explore_more_source_tables' => [
			'title' => 'Create Explore More Tables',
			'description' => 'Adds tables to control Explore More sources with sorting and visibility by library and location.',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS explore_more_source (
				id INT(11) NOT NULL AUTO_INCREMENT,
				source VARCHAR(50) NOT NULL,
				showInExploreMore TINYINT(1) NOT NULL DEFAULT 1,
				PRIMARY KEY (id),
				UNIQUE KEY (source)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_library (
				id INT(11) NOT NULL AUTO_INCREMENT,
				exploreMoreSourceId INT(11) NOT NULL,
				libraryId INT(11) NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY (exploreMoreSourceId, libraryId),
				FOREIGN KEY (exploreMoreSourceId) REFERENCES explore_more_source(id) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_location (
				id INT(11) NOT NULL AUTO_INCREMENT,
				exploreMoreSourceId INT(11) NOT NULL,
				locationId INT(11) NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY (exploreMoreSourceId, locationId),
				FOREIGN KEY (exploreMoreSourceId) REFERENCES explore_more_source(id) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_group (
				id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(100) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_entry (
				id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				exploreMoreSourceGroupId INT NOT NULL,
				exploreMoreSourceId INT NOT NULL,
				weight INT NOT NULL DEFAULT 0,
				FOREIGN KEY (exploreMoreSourceGroupId) REFERENCES explore_more_source_group(id) ON DELETE CASCADE,
				FOREIGN KEY (exploreMoreSourceId) REFERENCES explore_more_source(id) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			],
		], //create_explore_more_source_tables
		'add_explore_more_permissions' => [
			'title' => 'Add Explore More Permissions',
			'description' => 'Adds permissions needed to allow administration of Explore More.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Local Enrichment', 'Administer All Explore More', '', 40, 'Allows users to administer Explore More sources.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer All Explore More'))",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Local Enrichment', 'Administer Library Explore More', '', 40, 'Allows users to administer Explore More sources for their library.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='Library Admin'), (SELECT id from permissions where name='Administer Library Explore More'))",
			],
		], //add_explore_more_permissions
		'insert_default_explore_more_sources' => [
			'title' => 'Insert Default Explore More Sources',
			'description' => 'Populate the explore_more_source table with the default sources.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Catalog', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('EBSCO EDS', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('EBSCOhost', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Summon', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Gale', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('CloudSource', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Events', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Web Indexer', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Lists', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Open Archives', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Series', 1);",
				"INSERT INTO explore_more_source (source, showInExploreMore) VALUES ('Genealogy', 1);",
			],
		], //insert_default_explore_more_sources

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
