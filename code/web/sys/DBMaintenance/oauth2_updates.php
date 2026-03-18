<?php
/**
 * OAuth2 Server database updates
 */

function getOAuth2Updates() {
	return [
		'oauth2_create_tables' => [
			'title' => 'OAuth2 - Create OAuth2 tables',
			'description' => 'Create tables for OAuth2 server functionality',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS oauth2_clients (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL,
					client_id VARCHAR(255) NOT NULL UNIQUE,
					client_secret VARCHAR(255) NOT NULL,
					scopes TEXT,
					redirect_uri VARCHAR(2000),
					is_active TINYINT(1) DEFAULT 1,
					created_by INT,
					created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					INDEX idx_client_id (client_id),
					INDEX idx_is_active (is_active)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
				
				"CREATE TABLE IF NOT EXISTS oauth2_access_tokens (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					token_id VARCHAR(100) NOT NULL UNIQUE,
					user_id INT,
					client_id VARCHAR(255) NOT NULL,
					scopes TEXT,
					revoked TINYINT(1) DEFAULT 0,
					expires_at TIMESTAMP NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					INDEX idx_token_id (token_id),
					INDEX idx_user_id (user_id),
					INDEX idx_client_id (client_id),
					INDEX idx_revoked (revoked),
					INDEX idx_expires_at (expires_at)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
				
				"CREATE TABLE IF NOT EXISTS oauth2_auth_codes (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					code_id VARCHAR(100) NOT NULL UNIQUE,
					user_id INT,
					client_id VARCHAR(255) NOT NULL,
					scopes TEXT,
					redirect_uri VARCHAR(2000),
					code_challenge VARCHAR(128),
					code_challenge_method VARCHAR(10),
					revoked TINYINT(1) DEFAULT 0,
					expires_at TIMESTAMP NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					INDEX idx_code_id (code_id),
					INDEX idx_user_id (user_id),
					INDEX idx_client_id (client_id),
					INDEX idx_revoked (revoked),
					INDEX idx_expires_at (expires_at)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
				
				"CREATE TABLE IF NOT EXISTS oauth2_refresh_tokens (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					token_id VARCHAR(100) NOT NULL UNIQUE,
					access_token_id VARCHAR(100) NOT NULL,
					revoked TINYINT(1) DEFAULT 0,
					expires_at TIMESTAMP NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					INDEX idx_token_id (token_id),
					INDEX idx_access_token_id (access_token_id),
					INDEX idx_revoked (revoked),
					INDEX idx_expires_at (expires_at)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			]
		],
		
		'oauth2_add_permissions' => [
			'title' => 'OAuth2 - Add OAuth2 permissions',
			'description' => 'Add permission to manage OAuth2 clients',
			'sql' => [
				"INSERT IGNORE INTO permissions (sectionName, name, requiredModule, weight, description) VALUES 
				('System Administration', 'Administer OAuth2', '', 325, 'Controls if the user can manage OAuth2 clients and view OAuth2 settings.')"
			]
		],

		'oauth2_add_admin_menu' => [
			'title' => 'OAuth2 - Add admin menu entry',
			'description' => 'Add OAuth2 Clients to the admin menu',
			'continueOnError' => true,
			'sql' => [
				"INSERT IGNORE INTO admin_sections (name, label, section, weight) VALUES 
				('oauth2_clients', 'OAuth2 Clients', 'system_admin', 26)"
			]
		]
	];
}
