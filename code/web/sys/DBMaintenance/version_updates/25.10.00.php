<?php

/** @noinspection PhpUnused */
function getUpdates25_10_00(): array {
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
		'addOptionsForIndexing896To899AsSeries' => [
			'title' => 'Add Options For Indexing 896 To 899 As Series',
			'description' => 'Add Options For Indexing 896 To 899 As Series',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE indexing_profiles ADD COLUMN index896asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index897asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index898asSeries TINYINT(1) DEFAULT 1',
				'ALTER TABLE indexing_profiles ADD COLUMN index899asSeries TINYINT(1) DEFAULT 1'
			]
		], //addOptionsForIndexing896To899AsSeries
		'addHooplaRecordExtractionBatchSize' => [
			'title' => 'Add Hoopla Record Extraction Batch Size',
			'description' => 'Add Hoopla Record Extraction Batch Size',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE hoopla_settings ADD COLUMN recordExtractionBatchSize INT DEFAULT 500',
			]
		], //addHooplaRecordExtractionBatchSize
		'add_permission_for_econtent_sorting' => [
			'title' => 'Add permissions for eContent sorting',
			'description' => 'Add permissions for eContent sorting',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Grouped Work Display', 'Administer All eContent Sorting', '', 60, 'Allows users to change how eContent Sources are sorted within a grouped work for all libraries.')",
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Grouped Work Display', 'Administer Library eContent Sorting', '', 70, 'Allows users to change how eContent Sources are sorted within a grouped work for their library.')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer All eContent Sorting'))",
			]
		], //add_permission_for_econtent_sorting
		'create_econtent_sorting_tables' => [
			'title' => 'Create eContent sorting tables',
			'description' => 'Create eContent sorting tables',
			'continueOnError' => true,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS grouped_work_econtent_sort_group (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL UNIQUE,
					sortAvailableSourcesFirst TINYINT(1) DEFAULT 1,
					sortMethod TINYINT(1) DEFAULT 1
				)',
				'CREATE TABLE IF NOT EXISTS grouped_work_econtent_sort (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					eContentSortingGroupId INT(11) NOT NULL,
					eContentSource VARCHAR(255) NOT NULL,
					weight INT(11) NOT NULL,
					UNIQUE(eContentSortingGroupId, eContentSource)
				)',
			],
		], //create_econtent_sorting_tables
		'create_default_econtent_sorting' => [
			'title' => 'Create default eContent sorting',
			'description' => 'Create default eContent sorting',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO grouped_work_econtent_sort_group (id, name, sortAvailableSourcesFirst, sortMethod) VALUES (1, 'Default', 1, 1)"
			]
		], //create_default_econtent_sorting
		'link_econtent_sorting_to_display_settings' => [
			'title' => 'Link eContent sorting to display settings',
			'description' => 'Link eContent sorting to display settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE grouped_work_display_settings ADD COLUMN eContentSortingGroupId INT(11) DEFAULT 1'
			]
		], //link_econtent_sorting_to_display_settings
		'add_series_sort_method' => [
			'title' => 'Add series sorting method',
			'description' => 'Add series sorting method',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE series ADD COLUMN sortMethod TINYINT DEFAULT 1'
			]
		], //add_series_sort_method

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater
		'add_hoopla_configurable_indexing_time' => [
			'title' => 'Add Configurable Hoopla Indexing Time',
			'description' => 'Add Hoopla Indexing Time',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE hoopla_settings ADD COLUMN indexingTime INT DEFAULT 1',
			]
		], //add_hoopla_configurable_indexing_time

		// Leo Stoyanov - BWS
		'add_num_total_entries_to_show_in_more_to_grouped_work_facet' => [
			'title' => 'Add Total Num Entries To Show In More To Grouped Work Facet',
			'description' => 'Add configurable field to control how many facet values show in the "More..." popup/expansion.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE grouped_work_facet ADD COLUMN numTotalEntriesToShowInMore INT(11) NOT NULL DEFAULT 30',
			]
		], // add_num_total_entries_to_show_in_more_to_grouped_work_facet

		//alexander - Open Fifth

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
