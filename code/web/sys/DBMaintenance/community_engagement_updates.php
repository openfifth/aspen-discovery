<?php
/**@noinspection SqlResolve*/
function getCommunityEngagementUpdates() {
    return [
        'community_builder_module' => [
			'title' => 'Community Module',
			'description' => 'Create Community Module',
			'sql' => [
				"INSERT INTO modules (name, indexName, backgroundProcess) VALUES ('Community', '', '')",
			],
		],
        'create_campaigns' => [
            'title' => 'Create Campaigns',
            'description' => 'Add table for campaigns',
            'sql' => [
                "CREATE TABLE ce_campaign (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(255),
                    startDate DATE NULL,
                    endDate DATE NULL,
                    enrollmentCounter INT(11) DEFAULT 0,
                    unenrollmentCounter INT(11) DEFAULT 0,
                    currentEnrollments INT NOT NULL DEFAULT 0,
                    campaignReward INT(11) DEFAULT -1
                ) ENGINE = InnoDB",
            ],
        ],
        'create_milestones' => [
            'title' => 'Create Milestones',
            'description' => 'Add table for milestones',
            'sql' => [
                "CREATE TABLE ce_milestone (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    conditionalField VARCHAR(100),
                    conditionalOperator VARCHAR(100),
                    conditionalValue VARCHAR(100),
                    milestoneType VARCHAR(100),
                    campaignId INT
                ) ENGINE = InnoDB",
            ],
        ],
        'create_reward_table' => [
            'title' => 'Create Reward Table',
            'description' => 'Create a table to store types of reward',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_reward (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(255),
                    rewardType INT(11) DEFAULT -1
                )ENGINE = InnoDB",
            ],
        ],
        'add_ce_campaign_milestone_progress_entries' => [
            'title' => 'Add add_ce_campaign_milestone_progress_entries database table',
            'description' => 'Store milestone progress entries to be processed by cronjob',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_campaign_milestone_progress_entries (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     userId INT NOT NULL,
                     ce_campaign_id INT NOT NULL,
                     ce_milestone_id INT NOT NULL,
                     ce_campaign_milestone_users_progress_id INT NOT NULL,
                     tableName VARCHAR(100),
                     processed TINYINT DEFAULT 0,
                     object MEDIUMTEXT
                )ENGINE = InnoDB",
            ],
        ],
        'add_ce_campaign_milestone_users_progress' => [
            'title' => 'Add add_ce_campaign_milestone_users_progress database table',
            'description' => 'Store milestone progress for each user',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_campaign_milestone_users_progress (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     userId INT NOT NULL,
                     ce_campaign_id INT NOT NULL,
                     ce_milestone_id INT NOT NULL,
                     progress INT NOT NULL,
                     rewardGiven TINYINT DEFAULT 0
                )ENGINE = InnoDB",
            ],
        ],
        'create_user_campaign_table' => [
            'title' => 'Create User Campaign Table',
            'description' => 'Create a table to link users and campaigns',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_user_campaign (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    userId INT NOT NULL,
                    campaignId INT NOT NULL,
                    enrollmentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
                    unenrollmentDate DATETIME,
                    completed TINYINT DEFAULT 0,
                    rewardGiven TINYINT DEFAULT 0
                )ENGINE = InnoDB",
            ],
        ],
        'add_campaign_milestones_table' => [
            'title' => 'Add Campaign Milestones Table',
            'description' => 'Add a new table to link campaigns and milestones',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_campaign_milestones (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    campaignId INT NOT NULL, 
                    milestoneId INT NOT NULL,
                    goal INT DEFAULT 0,
                    reward INT(11) DEFAULT -1
                )ENGINE = InnoDB",
            ],
        ],
        'create_user_completed_milestones_table' => [
            'title' => 'Create User Completed Milestones Table',
            'description' => 'Add table to store completed milestone information',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_user_completed_milestones (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    userId INT NOT NULL,
                    milestoneId INT NOT NULL, 
                    campaignId INT NOT NULL, 
                    completedAt DATETIME NOT NULL
                )ENGINE = InnoDB",
            ],
        ],
        'add_campaign_data_table' => [
            'title' => 'Add Campaign Data Table',
            'description' => 'Add campaign data table for dashboard',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_campaign_data (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                    campaignId INT NOT NULL, 
                    month INT(2) NOT NULL, 
                    year INT(4) NOT NULL, 
                    totalEnrollments INT NOT NULL, 
                    currentEnrollments INT NOT NULL, 
                    totalUnenrollments INT NOT NULL,
                    instance VARCHAR(100)
                )ENGINE = InnoDB",
            ],
        ],
        'add_table_for_user_campaign_data' => [
            'title' => 'Add Table For User Campaign Data',
            'description' => 'Add user campaign data table for dashboard',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_user_campaign_data (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                    userId INT NOT NULL, 
                    month INT(2) NOT NULL, 
                    year INT(4) NOT NULL, 
                    enrollmentCount INT(11) DEFAULT 0,
                    campaignId INT NOT NULL,
                    instance VARCHAR(100)
                )ENGINE = InnoDB",
            ],
        ],
        'community_engagement_roles' => [
            'title' => 'Community Engagement Module Roles and Permissions',
            'description' => 'Set up roles and permissions for the Community Engagement Module',
            'sql' => [
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
                    ('Community', 'Administer Community Module',' Community', 180, 'Allows the user to create rewards, milestones and campaigns.')"
            ],
        ],
        'add_role_permissions_for_community_engagement_module' => [
            'title' => 'Add Role Permissions For Community Engagement Module',
            'description' => 'Set up role permissions to administer the Community Engagement module',
            'sql' => [
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Community Module'))"
            ],
        ],
        'view_community_dashboard_permissions' => [
            'title' => 'View Community Dashboard Permissions',
            'description' => 'Set up permissions to restrict who can view the community dashboard',
            'sql' => [
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES 
                    ('Reporting', 'View Community Dashboard', 'Community', 190, 'Allows the user to view the community engagement dashboard.')"
            ],
        ],
        'add_role_permissions_for_community_engagement_dashboard' => [
            'title' => 'Add Role Permissions For Community Engagement Dashboard',
            'description' => 'Set up role permissions for Community Engagement Dashboard',
            'sql' => [
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='View Community Dashboard'))"
            ],
        ],
        # !! DEV ONLY !!
        'add_test_data' => [
            'title' => 'Add Test Data',
            'description' => 'Add test data to the community engagement tables',
            'sql' => [
                # Insert campaigns
                "INSERT INTO ce_campaign (name, description, startDate, endDate) VALUES
                    ('Campaign 1: Active', 'This is a test campaign', '2022-01-01 00:00:00', '2026-01-31 00:00:00'),
                    ('Campaign 2: Active', 'This is a test campaign', '2022-01-01 00:00:00', '2026-01-31 00:00:00'),
                    ('Campaign 3: Past', 'This is a test campaign', '2022-01-01 00:00:00', '2022-01-31 00:00:00'),
                    ('Campaign 4: Long future', 'This is a test campaign in the long future ', '2026-01-01 00:00:00', '2028-01-31 00:00:00'),
                    ('Campaign 5: Active', 'This is a test campaign', '2022-01-01 00:00:00', '2028-01-31 00:00:00'),
                    ('Campaign 6: Upcoming', 'This is an upcoming test campaign (as of Nov 2024)', '2024-11-30 00:00:00', '2024-12-30 00:00:00'),
                    ('Campaign 7: Not accessible to CPL', 'Campaign not accessible to CPL', '2024-11-02 00:00:00', '2026-12-30 00:00:00')",
                # Enroll user 3 (ktd cardnumber: 42) to some campaigns
                "INSERT INTO ce_user_campaign (userId, campaignId, enrollmentDate) VALUES
                    (3, 5, '2024-11-06 16:55:26'),
                    (3, 2, '2024-11-06 16:55:26')",
                # Grant library access to campaigns (libraryId: 2 = Koha's Centerville)
                "INSERT INTO ce_campaign_library_access (campaignId, libraryId) VALUES
                    (1, 2),
                    (2, 2),
                    (3, 2),
                    (4, 2),
                    (5, 2),
                    (6, 2),
                    (7, 3)",
                # Grant patron type access to campaigns (patronTypeId: 8 = 'S' Koha's Staff category)
                "INSERT INTO ce_campaign_patron_type_access (campaignId, patronTypeId) VALUES
                    (1, 8),
                    (2, 8),
                    (3, 8),
                    (4, 8),
                    (5, 8),
                    (6, 8),
                    (7, 3)",
                # Insert milestones
                "INSERT INTO ce_milestone (name, conditionalField, conditionalValue, conditionalOperator, milestoneType, campaignId) VALUES
                    ('Milestone 1: checkout with condition', 'title_display', 'Title', 'equals', 'user_checkout', 1),
                    ('Milestone 2: hold with condition', 'author_display', 'Author', 'equals', 'user_hold', 2),
                    ('Milestone 3: checkout with condition', 'author_display', 'Author', 'equals', 'user_checkout', 2),
                    ('Milestone 4: checkout without condition', 'author_display', '', 'equals', 'user_checkout', 2),
                    ('Milestone 5: review with condition', 'subject_facet', 'Subject', 'equals', 'user_work_review', 3),
                    ('Milestone 6: checkout with condition', 'user_list', '1', 'equals', 'user_checkout', 3),
                    ('Milestone 7: checkout with condition', 'user_list', '1', 'equals', 'user_checkout', 5)",
                # Link milestones to campaigns
                "INSERT INTO ce_campaign_milestones (campaignId, milestoneId, goal, reward) VALUES
                    (1, 1, 10, -1),
                    (2, 2, 10, -1),
                    (2, 3, 10, -1),
                    (2, 4, 15, -1),
                    (3, 3, 10, -1),
                    (3, 4, 10, -1),
                    (5, 5, 12, -1)",
                # Insert rewards
                "INSERT INTO ce_reward (name, description, rewardType) VALUES
                    ('Test Reward 1', 'This is a test reward', 0),
                    ('Test Reward 2', 'This is a test reward', 0),
                    ('Test Reward 3', 'This is a test reward', 0)",
                # Insert some users milestones progress
                "INSERT INTO ce_campaign_milestone_users_progress (userId, ce_campaign_id, ce_milestone_id, progress, rewardGiven) VALUES
                    (3, 5, 1, 4, 1),
                    (3, 2, 2, 6, 0),
                    (3, 2, 3, 3, 1),
                    (3, 2, 4, 8, 0)",
                # Enable the community module
                "UPDATE modules SET enabled = 1 WHERE name = 'Community'"
            ],
        ],
        'add_image_uploads_to_rewards' => [
            'title' => 'Add Image Uploads To Rewards',
            'description' => 'Allow image uploads for digital badges',
            'sql' => [
                "ALTER TABLE ce_reward ADD COLUMN badgeImage VARCHAR(255) NULL"
            ],
        ],
    ];
}