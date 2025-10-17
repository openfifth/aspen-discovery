<?php

/**
 * Docker logging class for Aspen Discovery services
 * Matches service names: apache, cron, backend
 */
class DockerLogger {
	
	private static $serviceName = 'BACKEND';
	
	/**
	 * Initialize logger with service name
	 */
	public static function init($serviceName = 'BACKEND') {
		self::$serviceName = strtoupper($serviceName);
	}
	
	/**
	 * Main logging function
	 */
	private static function log($level, $message) {
		$timestamp = date('Y-m-d H:i:s');
		echo "[{$timestamp}] [" . self::$serviceName . "] [{$level}] {$message}" . PHP_EOL;
	}
	
	/**
	 * Log info message
	 */
	public static function info($message) {
		self::log('INFO', $message);
	}
	
	/**
	 * Log warning message
	 */
	public static function warn($message) {
		self::log('WARN', $message);
	}
	
	/**
	 * Log error message and exit
	 */
	public static function error($message) {
		self::log('ERROR', $message);
		exit(1);
	}
}

// Auto-initialize
DockerLogger::init();
?>
