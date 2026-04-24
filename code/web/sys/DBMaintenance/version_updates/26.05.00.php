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
		'migrate_old_mpaa_rating_to_content_rating' => [
			'title' => 'Migrate old mpaa_rating to content_rating',
			'description' => 'Migrate old mpaa_rating to content_rating',
			'sql' => [
				"UPDATE grouped_work_facet SET facetName = 'content_rating', displayName = 'Content Rating', displayNamePlural = 'Content Ratings' WHERE facetName = 'mpaa_rating';",
			],
		], //migrate_old_mpaa_rating_to_content_rating

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
