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
