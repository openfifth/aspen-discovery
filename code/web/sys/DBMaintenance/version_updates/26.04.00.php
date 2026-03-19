<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_04_00(): array {
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
		'Add OAuth2 Server' => [
			'title' => 'Add OAuth2 Server',
			'description' => 'Add OAuth2 Server',
			'continueOnError' => false,
			'sql' => [
				"CREATE TABLE IF NOT EXISTS oauth2_clients (
				id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				client_id VARCHAR(255) NOT NULL UNIQUE,
				client_secret VARCHAR(255) NOT NULL,
				client_type ENUM('web_application', 'native_application', 'service_application') DEFAULT 'web_application',
				scopes TEXT,
				redirect_uri VARCHAR(2000),
				is_active TINYINT(1) DEFAULT 1,
				created_by INT,
				created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				INDEX idx_client_id (client_id),
				INDEX idx_client_type (client_type),
				INDEX idx_is_active (is_active),
				INDEX idx_created_by (created_by)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS oauth2_access_tokens (
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
				INDEX idx_expires_at (expires_at),
				FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS oauth2_auth_codes (
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
				INDEX idx_expires_at (expires_at),
				FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS oauth2_refresh_tokens (
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS oauth2_rate_limits (
				id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				client_id VARCHAR(255) NOT NULL,
				ip_address VARCHAR(45) NOT NULL,
				endpoint VARCHAR(100) NOT NULL,
				request_count INT DEFAULT 0,
				window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY unique_rate_limit (client_id, ip_address, endpoint),
				INDEX idx_client_id (client_id),
				INDEX idx_endpoint (endpoint),
				INDEX idx_window_start (window_start),
				INDEX idx_last_request (last_request)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			INSERT IGNORE INTO permissions (sectionName, name, requiredModule, weight, description) VALUES
			('System Administration', 'Administer OAuth2', '', 325, 'Controls if the user can manage OAuth2 clients and view OAuth2 settings.');
			
			ALTER TABLE oauth2_access_tokens ADD INDEX idx_user_client (user_id, client_id);
			ALTER TABLE oauth2_auth_codes ADD INDEX idx_user_client (user_id, client_id);
			"
			]
		],
		//Add OAuth2 Server
		'Add OpenID Connect' => [
			'title' => 'Add OpenID Connect',
			'description' => 'Add OpenID Connect support to OAuth2',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE oauth2_clients ADD COLUMN supports_openid TINYINT(1) DEFAULT 0;
				ALTER TABLE oauth2_clients ADD COLUMN allowed_claims TEXT;
				ALTER TABLE oauth2_clients ADD INDEX idx_supports_openid (supports_openid);"
			]
		],
		//Add OpenID Connect

		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//mark j

		//lucas

		//tomas

		// stephen

		//other


	];
}
