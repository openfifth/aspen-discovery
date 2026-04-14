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
				"CREATE TABLE IF NOT EXISTS explore_more_source (\n"
				. "  id INT(11) NOT NULL AUTO_INCREMENT,\n"
				. "  source VARCHAR(50) NOT NULL,\n"
				. "  weight INT(11) NOT NULL DEFAULT 0,\n"
				. "  showInExploreMore TINYINT(1) NOT NULL DEFAULT 1,\n"
				. "  PRIMARY KEY (id),\n"
				. "  UNIQUE KEY (source)\n"
				. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_library (\n"
				. "  id INT(11) NOT NULL AUTO_INCREMENT,\n"
				. "  exploreMoreSourceId INT(11) NOT NULL,\n"
				. "  libraryId INT(11) NOT NULL,\n"
				. "  PRIMARY KEY (id),\n"
				. "  UNIQUE KEY (exploreMoreSourceId, libraryId),\n"
				. "  FOREIGN KEY (exploreMoreSourceId) REFERENCES explore_more_source(id) ON DELETE CASCADE\n"
				. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
				"CREATE TABLE IF NOT EXISTS explore_more_source_location (\n"
				. "  id INT(11) NOT NULL AUTO_INCREMENT,\n"
				. "  exploreMoreSourceId INT(11) NOT NULL,\n"
				. "  locationId INT(11) NOT NULL,\n"
				. "  PRIMARY KEY (id),\n"
				. "  UNIQUE KEY (exploreMoreSourceId, locationId),\n"
				. "  FOREIGN KEY (exploreMoreSourceId) REFERENCES explore_more_source(id) ON DELETE CASCADE\n"
				. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
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
			'description' => 'Populate the explore_more_source table with the default sources and weights.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Catalog', 0, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('EBSCO EDS', 1, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('EBSCOhost', 2, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Summon', 3, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Gale', 4, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('CloudSource', 5, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Events', 6, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Web Indexer', 7, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Lists', 8, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Open Archives', 9, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Series', 10, 1);",
				"INSERT INTO explore_more_source (source, weight, showInExploreMore) VALUES ('Genealogy', 11, 1);",
			],
		], //insert_default_explore_more_sources

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
