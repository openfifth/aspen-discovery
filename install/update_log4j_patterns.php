<?php

if (count($_SERVER['argv']) > 1) {
	$serverName = $_SERVER['argv'][1];
	$confPath = '/usr/local/aspen-discovery/sites/' . $serverName . '/conf/';

	if (!is_dir($confPath)) {
		echo "Configuration directory not found at $confPath\n";
		exit(0);
	}

	$log4jFiles = glob($confPath . 'log4j*.properties');

	if (empty($log4jFiles)) {
		echo "No log4j properties files found in $confPath\n";
		exit(0);
	}

	$updatedFiles = 0;
	$errorMessages = [];

	foreach ($log4jFiles as $log4jFile) {
		try {
			$content = file_get_contents($log4jFile);
			$originalContent = $content;

			// Update console pattern: %m%n -> %m%n%throwable{short}%n.
			$content = preg_replace(
				'/^(appender\.console\.layout\.pattern\s*=.*%m%n)(?!%throwable)(.*)$/m',
				'$1%throwable{short}%n$2',
				$content
			);

			// Update rolling file pattern: %m%n -> %m%n%throwable{short}%n.
			$content = preg_replace(
				'/^(appender\.rolling\.layout\.pattern\s*=.*%m%n)(?!%throwable)(.*)$/m',
				'$1%throwable{short}%n$2',
				$content
			);

			if ($content !== $originalContent) {
				if (file_put_contents($log4jFile, $content) !== false) {
					$updatedFiles++;
					echo "Updated " . basename($log4jFile) . "\n";
				} else {
					$errorMessages[] = basename($log4jFile);
				}
			}
		} catch (Exception $e) {
			$errorMessages[] = basename($log4jFile) . ': ' . $e->getMessage();
		}
	}

	echo "Updated $updatedFiles log4j properties file(s) for $serverName\n";
	if (!empty($errorMessages)) {
		echo "Errors: " . implode(', ', $errorMessages) . "\n";
	}
} else {
	echo "Usage: php update_log4j_patterns.php {serverName}\n";
	exit(1);
}