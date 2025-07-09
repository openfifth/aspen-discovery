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

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth

		//chloe - Open Fifth
		'move_heycentric_permission' => [
			 'title' => 'Move HeyCentric Permission',
			 'description' => 'Move the Administrer HeyCentric Settings permission into the existing eCommerce section',
			 'continueOnError' => false,
			 'sql' => [
				"UPDATE permissions SET name='Administer HeyCentric', sectionName='eCommerce', description='Allows the user to administer the integration with HeyCentric <em>This has potential security and cost implications.</em>' WHERE name='Administer HeyCentric Settings' AND sectionName='ecommerce'",
			 ],
			 
		 ], // move_heycentric_permission


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
