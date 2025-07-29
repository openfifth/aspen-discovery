<?php

function getUpdatesQ3_25_00_00(): array {
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

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		//Mark - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater
		'remove_starRating_from_overdrive_api_product_metadata' => [
			'title' => 'Remove Star Rating from overdrive_api_product_metadata',
			'description' => 'Remove starRating from overdrive_api_product_metadata table.',
			'sql' => [
				"ALTER TABLE overdrive_api_product_metadata DROP COLUMN starRating",
			]
		], //remove_starRating_from_overdrive_api_product_metadata

		// Leo Stoyanov - BWS
		'enable_web_builder_for_libraries_using_it' => [
			'title' => 'Enable Web Builder for Libraries Using Web Builder Features',
			'description' => 'Enable Web Builder only for libraries that have associated Web Builder content (resources, pages, forms, etc.).',
			'continueOnError' => true,
			'sql' => [
				"UPDATE library SET enableWebBuilder = 1 
				WHERE libraryId IN (
					-- Libraries with Web Resources
					SELECT DISTINCT libraryId FROM library_web_builder_resource
					UNION
					-- Libraries with Basic Pages  
					SELECT DISTINCT libraryId FROM library_web_builder_basic_page
					UNION
					-- Libraries with Portal Pages (Custom Pages)
					SELECT DISTINCT libraryId FROM library_web_builder_portal_page
					UNION
					-- Libraries with Custom Web Resource Pages
					SELECT DISTINCT libraryId FROM library_web_builder_custom_web_resource_page
					UNION
					-- Libraries with Custom Forms
					SELECT DISTINCT libraryId FROM library_web_builder_custom_form
					UNION
					-- Libraries with Grapes Pages
					SELECT DISTINCT libraryId FROM library_web_builder_grapes_page
					UNION
					-- Libraries with Quick Polls
					SELECT DISTINCT libraryId FROM library_web_builder_quick_poll
				)",
			],
		],
		'update_record_to_include_defaults' => [
			'title' => 'Update RecordToInclude Column Defaults to Match PHP Defaults',
			'description' => 'Update database column defaults for includeHoldableOnly, includeItemsOnOrder, and includeEContent to match the PHP class defaults.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library_records_to_include CHANGE COLUMN includeHoldableOnly includeHoldableOnly tinyint(1) NOT NULL DEFAULT 0',
				'ALTER TABLE library_records_to_include CHANGE COLUMN includeItemsOnOrder includeItemsOnOrder tinyint(1) NOT NULL DEFAULT 1',
				'ALTER TABLE library_records_to_include CHANGE COLUMN includeEContent includeEContent tinyint(1) NOT NULL DEFAULT 1',
				'ALTER TABLE location_records_to_include CHANGE COLUMN includeHoldableOnly includeHoldableOnly tinyint(1) NOT NULL DEFAULT 0',
				'ALTER TABLE location_records_to_include CHANGE COLUMN includeItemsOnOrder includeItemsOnOrder tinyint(1) NOT NULL DEFAULT 1',
				'ALTER TABLE location_records_to_include CHANGE COLUMN includeEContent includeEContent tinyint(1) NOT NULL DEFAULT 1',
			]
		], //update_record_to_include_defaults
		'update_browse_category_sort_options' => [
			'title' => 'Update Browse Category Sort Options for Lists Search',
			'description' => 'Add new date sorting options for browse categories when using Lists as search source.',
			'sql' => [
				"ALTER TABLE browse_category MODIFY COLUMN defaultSort ENUM('relevance','popularity','newest_to_oldest','author','title','user_rating','holds','publication_year_desc','publication_year_asc','event_date','oldest_to_newest','newest_updated_to_oldest','oldest_updated_to_newest') DEFAULT 'relevance'"
			]
		], //update_browse_category_sort_options
		'update_collection_spotlight_sort_options' => [
			'title' => 'Update Collection Spotlight Sort Options for Lists Search',
			'description' => 'Add new date sorting options for collection spotlights when using Lists as search source.',
			'sql' => [
				"ALTER TABLE collection_spotlight_lists MODIFY COLUMN defaultSort ENUM('relevance','popularity','newest_to_oldest','author','title','user_rating','holds','publication_year_desc','publication_year_asc','event_date','oldest_to_newest','newest_updated_to_oldest','oldest_updated_to_newest') DEFAULT 'relevance'"
			]
		], //update_collection_spotlight_sort_options

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'control_display_of_user_dropdown_in_community_engagement_admin_view' => [
			'title' => 'Control User Select Type in Admin View',
			'description' => 'Add options for how to select users in the admin view section',
			'sql' => [
				"ALTER TABLE library ADD COLUMN communityEngagementAdminUserSelect VARCHAR(20) DEFAULT 'dropdown'",
			],
		], //control_display_of_user_dropdown_in_community_engagement_admin_view
        'display_only_users_from_current_library_in_user_search_admin_view' => [
			'title' => 'Display Only Users From Current Library in User Search Admin View',
			'description' => 'Add option to display users from all libraries or only the current library location when searching by user in Admin View',
			'sql' => [
				"ALTER TABLE library ADD COLUMN displayOnlyUsersForLocationInUserAdmin TINYINT(1) DEFAULT 0",
			],
		], //display_only_users_from_current_library_in_user_search_admin_view
        'allow_admin_to_enroll_users_via_admin_view' => [
			'title' => 'Allow Admin To Enroll Users Via Admin View',
			'description' => 'Add control over whether admin can enroll users via the admin view page',
			'sql' => [
				"ALTER TABLE library ADD COLUMN allowAdminToEnrollUsersInAdminView TINYINT(1) DEFAULT 0",
			],
		], //allow_admin_to_enroll_users_via_admin_view
		'increase_location_display_name_allowed_length' => [
			'title' => 'Increase Location Display Name Allowed Length',
			'description' => 'Increase the allowed length for the location display name',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE location MODIFY displayName VARCHAR(100) NOT NULL'
			],
		], // increase_location_display_name_allowed_length
		'add_title_to_user_work_review' => [
			'title' => 'Add Title To user Work Review',
			'description' => 'Add title of reviewed work to table',
			'sql' => [
				"ALTER TABLE user_work_review ADD COLUMN title VARCHAR(512) DEFAULT ''",
			]
		], //add_title_to_user_work_review

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


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
