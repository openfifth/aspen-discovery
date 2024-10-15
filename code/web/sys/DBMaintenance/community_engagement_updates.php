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
        'add_ce_milestone_progress_entries' => [
            'title' => 'Add add_ce_milestone_progress_entries database table',
            'description' => 'Store milestone progress entries to be processed by cronjob',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_milestone_progress_entries (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     userId INT NOT NULL,
                     ce_milestone_id INT NOT NULL,
                     ce_milestone_users_progress_id INT NOT NULL,
                     tableName VARCHAR(100),
                     processed TINYINT DEFAULT 0,
                     object MEDIUMTEXT
                )ENGINE = InnoDB",
            ],
        ],
        'add_ce_milestone_users_progress' => [
            'title' => 'Add add_ce_milestone_users_progress database table',
            'description' => 'Store milestone progress for each user',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS ce_milestone_users_progress (
                     id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     userId INT NOT NULL,
                     ce_milestone_id INT NOT NULL,
                     progress INT NOT NULL
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
    ];
}