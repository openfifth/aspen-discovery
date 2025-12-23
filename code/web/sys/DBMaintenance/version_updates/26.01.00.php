<?php
/** @noinspection SqlDialectInspection */

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
		'polaris_cancelled_holds' => [
			'title' => 'Polaris Cancelled Holds',
			'description' => 'Add option to show users their cancelled holds for Polaris',
			'sql' => [
				'ALTER TABLE library ADD COLUMN showCancelledHolds TINYINT(1) DEFAULT 1',
				'ALTER TABLE user_hold ADD COLUMN cancelled TINYINT(1)'
			]
		], //polaris_cancelled_holds
		'events_calendar_footer' => [
			'title' => 'Events Calendar Footer',
			'description' => 'Add column footer in calendar_display_settings and rename Print Calendars With Header Images permissions to include Footer.',
			'sql' => [
				"ALTER TABLE calendar_display_settings ADD COLUMN footer VARCHAR(500)",
				"UPDATE permissions set name='Print Calendars with Header Images and Footer' where name='Print Calendars with Header Images'"
			]
		], //events_calendar_footer
		'toggle_timestamps_for_events' => [
			'title' => 'Toggle Event Timestamps',
			'description' => 'Toggle Event Timestamps',
			'sql' => [
				'ALTER TABLE event ADD COLUMN hideTimestamps TINYINT(1) NOT NULL DEFAULT 0'
			]
		], //toggle_timestamps_for_events
		'full_month_names_events_calendar' => [
			'title' => 'Events Calendar Full Month Names',
			'description' => 'Add ability to display full month names on events calendars',
			'sql' => [
				'ALTER TABLE calendar_display_settings ADD COLUMN fullMonthName TINYINT NOT NULL DEFAULT 0'
			]
		], //full_month_names_events_calendar
		'events_calendar_title' => [
			'title' => 'Events Calendar Title',
			'description' => 'Add the ability to create titles for event calendars in Calendar Display Settings',
			'sql' => [
				"ALTER TABLE calendar_display_settings ADD COLUMN calendarTitle VARCHAR(255) DEFAULT 'Events Calendar'",
			]
		], //events_calendar_title

		//leo
		'image_uploads_hero_slider_fields' => [
			'title' => 'Image Uploads - Hero Slider Fields',
			'description' => 'Add fields to image_uploads for hero slider functionality.',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS aspectRatioWidth INT(11) DEFAULT NULL",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS aspectRatioHeight INT(11) DEFAULT NULL",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS calculatedAspectRatio DECIMAL(10,6) DEFAULT NULL",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS altText VARCHAR(512) DEFAULT NULL",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS pageLink VARCHAR(512) DEFAULT NULL",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS startDate INT(11) DEFAULT 0",
				"ALTER TABLE image_uploads ADD COLUMN IF NOT EXISTS endDate INT(11) DEFAULT 0",
				"ALTER TABLE image_uploads ADD INDEX IF NOT EXISTS aspectRatio(aspectRatioWidth, aspectRatioHeight)",
				"ALTER TABLE image_uploads ADD INDEX IF NOT EXISTS dateRange(startDate, endDate)",
			],
		], //image_uploads_hero_slider_fields
		'hero_slider_playlist' => [
			'title' => 'Hero Slider Playlists',
			'description' => 'Create table for hero slider playlists',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS hero_slider_playlist (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL,
					libraryId INT(11) DEFAULT -1,
					deleted TINYINT(1) DEFAULT 0,
					dateDeleted INT(11),
					deletedBy INT(11),
					INDEX libraryId(libraryId)
				) ENGINE=INNODB",
			],
		], //hero_slider_playlist
		'hero_slider_playlist_image' => [
			'title' => 'Hero Slider Playlist Images',
			'description' => 'Create junction table for playlist images.',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS hero_slider_playlist_image (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					playlistId INT(11) NOT NULL,
					imageId INT(11) NOT NULL,
					weight INT(11) NOT NULL DEFAULT 0,
					duration INT(11) NOT NULL DEFAULT 5,
					INDEX playlistId(playlistId),
					INDEX imageId(imageId),
					FOREIGN KEY (playlistId) REFERENCES hero_slider_playlist(id) ON DELETE CASCADE,
					FOREIGN KEY (imageId) REFERENCES image_uploads(id) ON DELETE CASCADE
				) ENGINE=INNODB",
			],
		], //hero_slider_playlist_image
		'hero_slider_location' => [
			'title' => 'Hero Slider Locations',
			'description' => 'Create table for hero slider locations/configurations.',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS hero_slider_location (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL,
					description TEXT,
					displayStyle ENUM('digital_signage', 'website') DEFAULT 'website',
					aspectRatioPreset VARCHAR(20) DEFAULT '16:9',
					aspectRatioWidth INT(11) NOT NULL DEFAULT 16,
					aspectRatioHeight INT(11) NOT NULL DEFAULT 9,
					autoRotate TINYINT(1) DEFAULT 1,
					rotationInterval INT(11) DEFAULT 5,
					playlistId INT(11),
					libraryId INT(11) DEFAULT -1,
					deleted TINYINT(1) DEFAULT 0,
					dateDeleted INT(11),
					deletedBy INT(11),
					INDEX playlistId(playlistId),
					INDEX libraryId(libraryId),
					FOREIGN KEY (playlistId) REFERENCES hero_slider_playlist(id) ON DELETE SET NULL
				) ENGINE=INNODB",
			],
		], //hero_slider_location
		'hero_slider_permissions' => [
			'title' => 'Hero Slider Permissions',
			'description' => 'Create permissions and permission group for hero slider management.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
					('Local Enrichment', 'Administer All Hero Sliders', '', 160, 'Allows the user to manage hero sliders for all libraries.'),
					('Local Enrichment', 'Administer Library Hero Sliders', '', 161, 'Allows the user to manage hero sliders for their home library.')
				",
				"INSERT INTO `permission_groups` (`groupKey`,`sectionName`,`label`,`description`) VALUES
					('adminHeroSliders','Local Enrichment','Administer Hero Sliders','Specify whether the role can manage all hero sliders or only those for the user''s home library.')",
				"INSERT IGNORE INTO `permission_group_permissions` (`groupId`,`permissionId`) SELECT pg.id, p.id FROM `permission_groups` pg JOIN `permissions` p ON p.name IN ('Administer All Hero Sliders','Administer Library Hero Sliders') WHERE pg.groupKey = 'adminHeroSliders'",
			],
		], //hero_slider_permissions
		'hero_slider_role_permissions' => [
			'title' => 'Hero Slider Role Permission',
			'description' => 'Assign hero slider permission to Opac Admin role.',
			'continueOnError' => false,
			'sql' => [
				"INSERT INTO role_permissions(roleId, permissionId) VALUES
					((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer All Hero Sliders'))",
			],
		], //hero_slider_role_permissions

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
