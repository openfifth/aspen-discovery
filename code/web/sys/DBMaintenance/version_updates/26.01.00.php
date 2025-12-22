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
		'library_control_holds_ready_for_pickup' => [
			'title' => 'Library - Add Control over Holds Ready For Pickup Section',
			'description' => 'Library - Add Control over Holds Ready For Pickup Section',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN showHoldsReadyForPickupSection TINYINT DEFAULT 1'
			]
		], //library_control_holds_ready_for_pickup

		//kirstien

		//kodi

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
