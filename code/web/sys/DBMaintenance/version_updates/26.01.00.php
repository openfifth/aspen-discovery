<?php

/** @noinspection PhpUnused */
function getUpdates26_01_00(): array {
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
		'full_month_names_events_calendar' => [
			'title' => 'Events Calendar Full Month Names',
			'description' => 'Add ability to display full month names on events calendars',
			'sql' => [
				'ALTER TABLE calendar_display_settings ADD COLUMN fullMonthName TINYINT NOT NULL DEFAULT 0'
			]
		], //full_month_names_events_calendar

		//leo

		//yanjun

		//imani

		//galen

		//alexander

		//chloe

		//mark j

		//lucas

		//tomas

		//other

	'user_payments_stripe_receipt_url' => [
		'title' => 'Add Stripe Receipt URL to User Payments',
		'description' => 'Add column to store Stripe receipt URL for payment receipts.',
		'continueOnError' => false,
		'sql' => [
			'ALTER TABLE user_payments ADD COLUMN IF NOT EXISTS stripeReceiptUrl VARCHAR(255) DEFAULT NULL'
		]
	], //user_payments_stripe_receipt_url

	];
}
