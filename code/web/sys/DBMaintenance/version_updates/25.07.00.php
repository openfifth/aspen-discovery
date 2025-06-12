<?php

function getUpdates25_07_00(): array {
	$curTime = time();
	return [
        'control_display_of_user_dropdown_in_community_engagement_admin_view' => [
			'title' => 'Control User Select Type in Admin View',
			'description' => 'Add options for how to select users in the admin view section',
			'sql' => [
				"ALTER TABLE library ADD COLUMN communityEngagementAdminUserSelect VARCHAR(20) DEFAULT 'dropdown'",
			],
		], //control_display_of_user_dropdown_in_community_engagement_admin_view
	];
}
