<?php

require_once ROOT_DIR . '/sys/OAuth2/OAuth2RateLimit.php';

/**
 * OAuth2 Rate Limiting Service
 * Implements sliding window rate limiting for OAuth2 endpoints
 */
class OAuth2RateLimiter {

	// Rate limit configurations per endpoint type
	private static $rateLimits = [
		'token' => [
			'requests' => 10,    // 10 requests
			'window' => 60,      // per 60 seconds (1 minute)
			'description' => 'Token endpoint rate limit'
		],
		'public_api' => [
			'requests' => 100,   // 100 requests  
			'window' => 3600,    // per 3600 seconds (1 hour)
			'description' => 'Public API rate limit'
		],
		'user_api' => [
			'requests' => 200,   // 200 requests
			'window' => 3600,    // per 3600 seconds (1 hour)  
			'description' => 'User API rate limit'
		],
		'auth' => [
			'requests' => 20,    // 20 requests
			'window' => 300,     // per 300 seconds (5 minutes)
			'description' => 'Authorization endpoint rate limit'
		],
		'register' => [
			'requests' => 5,     // 5 requests
			'window' => 3600,    // per 3600 seconds (1 hour)
			'description' => 'Client registration rate limit'
		]
	];

	/**
	 * Check if request should be rate limited
	 * @param string $endpoint The endpoint being accessed
	 * @param string $clientId OAuth2 client ID (optional)
	 * @param string $ipAddress Client IP address
	 * @return array ['allowed' => bool, 'headers' => array, 'resetTime' => int]
	 */
	public static function checkRateLimit(string $endpoint, string $clientId = '', string $ipAddress = ''): array {
		// Get rate limit config for endpoint
		$config = self::$rateLimits[$endpoint] ?? self::$rateLimits['public_api'];
		
		// Use IP as fallback identifier if no client ID
		$identifier = $clientId ?: $ipAddress;
		if (empty($identifier)) {
			$identifier = 'anonymous';
		}

		// Get or create rate limit record
		$rateLimitRecord = OAuth2RateLimit::getRateLimitRecord($identifier, $ipAddress, $endpoint);

		// Check if current window has expired
		if ($rateLimitRecord->isWindowExpired($config['window'])) {
			$rateLimitRecord->resetWindow();
		}

		// Calculate remaining requests
		$remaining = max(0, $config['requests'] - $rateLimitRecord->request_count);
		$resetTime = strtotime($rateLimitRecord->window_start) + $config['window'];

		// Prepare response headers
		$headers = [
			'X-RateLimit-Limit' => $config['requests'],
			'X-RateLimit-Remaining' => $remaining,
			'X-RateLimit-Reset' => $resetTime,
			'X-RateLimit-Window' => $config['window']
		];

		// Check if rate limit exceeded
		if ($rateLimitRecord->request_count >= $config['requests']) {
			$headers['Retry-After'] = $resetTime - time();
			return [
				'allowed' => false,
				'headers' => $headers,
				'resetTime' => $resetTime,
				'message' => "Rate limit exceeded. Try again in " . ($resetTime - time()) . " seconds."
			];
		}

		// Increment request count
		$rateLimitRecord->incrementCount();

		// Update remaining count after increment
		$headers['X-RateLimit-Remaining'] = max(0, $config['requests'] - $rateLimitRecord->request_count);

		return [
			'allowed' => true,
			'headers' => $headers,
			'resetTime' => $resetTime,
			'message' => 'Request allowed'
		];
	}

	/**
	 * Send rate limit response headers
	 */
	public static function sendHeaders(array $headers): void {
		foreach ($headers as $name => $value) {
			header("$name: $value");
		}
	}

	/**
	 * Send rate limit exceeded response
	 */
	public static function sendRateLimitResponse(array $rateLimitResult): void {
		http_response_code(429); // Too Many Requests
		header('Content-Type: application/json');
		
		// Send rate limit headers
		self::sendHeaders($rateLimitResult['headers']);

		echo json_encode([
			'error' => 'rate_limit_exceeded',
			'error_description' => $rateLimitResult['message'],
			'retry_after' => $rateLimitResult['headers']['Retry-After'] ?? 60
		]);
	}

	/**
	 * Apply rate limiting to an endpoint
	 * @param string $endpoint Endpoint name
	 * @param string $clientId OAuth2 client ID (optional)
	 * @return bool True if request allowed, false if rate limited (response already sent)
	 */
	public static function enforce(string $endpoint, string $clientId = ''): bool {
		$ipAddress = self::getClientIP();
		$rateLimitResult = self::checkRateLimit($endpoint, $clientId, $ipAddress);

		// Always send rate limit headers
		self::sendHeaders($rateLimitResult['headers']);

		if (!$rateLimitResult['allowed']) {
			self::sendRateLimitResponse($rateLimitResult);
			return false;
		}

		return true;
	}

	/**
	 * Get client IP address, handling proxies
	 */
	private static function getClientIP(): string {
		$ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
		
		foreach ($ipKeys as $key) {
			if (!empty($_SERVER[$key])) {
				$ip = $_SERVER[$key];
				// Handle comma-separated IPs (from proxies)
				if (strpos($ip, ',') !== false) {
					$ip = trim(explode(',', $ip)[0]);
				}
				// Validate IP address
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					return $ip;
				}
			}
		}

		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}

	/**
	 * Clean up expired rate limit records
	 */
	public static function cleanupExpiredRecords(): int {
		global $aspen_db;
		
		$cleanupQuery = "
			DELETE FROM oauth2_rate_limits 
			WHERE last_request < DATE_SUB(NOW(), INTERVAL 2 HOUR)
		";
		
		$result = $aspen_db->query($cleanupQuery);
		return $aspen_db->affected_rows;
	}

	/**
	 * Get rate limit statistics for monitoring
	 */
	public static function getStatistics(): array {
		global $aspen_db;
		
		$stats = [];
		
		// Get current active rate limit records
		$query = "
			SELECT 
				endpoint,
				COUNT(*) as active_clients,
				SUM(request_count) as total_requests,
				AVG(request_count) as avg_requests_per_client
			FROM oauth2_rate_limits 
			WHERE window_start > DATE_SUB(NOW(), INTERVAL 1 HOUR)
			GROUP BY endpoint
		";
		
		$result = $aspen_db->query($query);
		if ($result) {
			while ($row = $result->fetch_assoc()) {
				$stats[$row['endpoint']] = [
					'active_clients' => (int)$row['active_clients'],
					'total_requests' => (int)$row['total_requests'],
					'avg_requests_per_client' => round((float)$row['avg_requests_per_client'], 2)
				];
			}
		}

		return $stats;
	}

	/**
	 * Update rate limit configuration (for admin interface)
	 */
	public static function updateRateLimit(string $endpoint, int $requests, int $window): bool {
		if (!isset(self::$rateLimits[$endpoint])) {
			return false;
		}

		self::$rateLimits[$endpoint]['requests'] = max(1, $requests);
		self::$rateLimits[$endpoint]['window'] = max(60, $window);
		
		// In production, this should be stored in database or config file
		// For now, it only affects current request
		return true;
	}

	/**
	 * Get current rate limit configuration
	 */
	public static function getRateLimitConfig(): array {
		return self::$rateLimits;
	}
}
